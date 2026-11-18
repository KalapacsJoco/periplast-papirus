<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Machine;
use App\Models\Mould;
use App\Models\MouldRepairRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\MouldRepairNotification;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class MouldRepairWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_repair_is_assigned_worked_on_and_tested_before_reactivation(): void
    {
        Notification::fake();
        $coordinator = User::factory()->create(['role' => Role::Coordinator]);
        $programmer = User::factory()->create(['role' => Role::Programmer]);
        $repairer = User::factory()->create(['role' => Role::Reparation]);
        $maintenance = User::factory()->create(['role' => Role::Maintenance]);
        $operator = User::factory()->create(['role' => Role::Operator]);
        $mould = $this->mould('warning');

        $this->createRequest($coordinator, $mould, 'reparation');
        $request = $mould->repairRequests()->firstOrFail();
        $this->assertSame('under_repair', $mould->fresh()->status);
        Notification::assertSentTo($programmer, MouldRepairNotification::class);
        Notification::assertSentTo($repairer, MouldRepairNotification::class);
        Notification::assertNotSentTo($maintenance, MouldRepairNotification::class);

        Livewire::actingAs($maintenance)->test('production-dashboard')
            ->call('setMouldRepairWorkStatus', $request->id, 'in_progress')->assertForbidden();
        Livewire::actingAs($repairer)->test('production-dashboard')
            ->call('setMouldRepairWorkStatus', $request->id, 'in_progress')
            ->set("repairWorkDescriptions.$request->id", 'Insert repaired')
            ->set("repairHours.$request->id", 1)
            ->set("repairMinutes.$request->id", 20)
            ->call('finishMouldRepair', $request->id)->assertHasNoErrors();
        $this->assertSame('awaiting_test', $request->fresh()->status);
        $this->assertSame('under_repair', $mould->fresh()->status);
        $this->assertSame(80, $request->attempts()->firstOrFail()->duration_minutes);

        Livewire::actingAs($operator)->test('production-dashboard')
            ->call('testMouldRepair', $request->id, true)->assertHasNoErrors();
        $this->assertSame('completed', $request->fresh()->status);
        $this->assertSame('active', $mould->fresh()->status);
        $this->assertTrue($request->attempts()->firstOrFail()->test_passed);
        Livewire::actingAs($operator)->test('production-dashboard')
            ->call('selectTab', 'maintenance')->assertSee('Insert repaired');
    }

    public function test_failed_test_requires_a_coordinator_and_preserves_every_attempt(): void
    {
        Notification::fake();
        $coordinator = User::factory()->create(['role' => Role::Coordinator]);
        $repairer = User::factory()->create(['role' => Role::Reparation]);
        $maintenance = User::factory()->create(['role' => Role::Maintenance]);
        $quality = User::factory()->create(['role' => Role::QualityManager]);
        $mould = $this->mould('needs_repair');
        $this->createRequest($coordinator, $mould, 'reparation');
        $request = $mould->repairRequests()->firstOrFail();
        $this->finish($repairer, $request, 'First repair');

        Livewire::actingAs($quality)->test('production-dashboard')
            ->call('testMouldRepair', $request->id, false)->assertHasErrors(["repairTestNotes.$request->id"])
            ->set("repairTestNotes.$request->id", 'Still leaking')
            ->call('testMouldRepair', $request->id, false)->assertHasNoErrors();
        $this->assertSame('failed', $request->fresh()->status);
        $this->assertSame('under_repair', $mould->fresh()->status);

        Livewire::actingAs($repairer)->test('production-dashboard')
            ->set("repairReopenRoles.$request->id", 'maintenance')
            ->call('reopenMouldRepair', $request->id)->assertForbidden();
        Livewire::actingAs($coordinator)->test('production-dashboard')
            ->set("repairReopenRoles.$request->id", 'maintenance')
            ->call('reopenMouldRepair', $request->id)->assertHasNoErrors();
        $this->finish($maintenance, $request, 'Second repair');
        Livewire::actingAs($quality)->test('production-dashboard')
            ->call('testMouldRepair', $request->id, true)->assertHasNoErrors();
        $this->assertSame('active', $mould->fresh()->status);
        $this->assertSame(['First repair', 'Second repair'], $request->attempts()->orderBy('id')->pluck('description')->all());
        $this->assertSame('Still leaking', $request->attempts()->oldest('id')->firstOrFail()->test_note);
    }

    public function test_parallel_requests_keep_mould_stopped_until_all_pass_and_block_manual_resolution(): void
    {
        Notification::fake();
        $coordinator = User::factory()->create(['role' => Role::Coordinator]);
        $repairer = User::factory()->create(['role' => Role::Reparation]);
        $mould = $this->mould('warning');
        $this->createRequest($coordinator, $mould, 'reparation');
        $this->createRequest($coordinator, $mould->fresh(), 'reparation');
        [$first, $second] = $mould->repairRequests()->orderBy('id')->get()->all();
        $this->finish($repairer, $first, 'First');
        Livewire::actingAs($coordinator)->test('production-dashboard')->call('testMouldRepair', $first->id, true);
        $this->assertSame('under_repair', $mould->fresh()->status);
        try {
            $mould->fresh()->update(['status' => 'active']);
            $this->fail('An open repair must block manual reactivation.');
        } catch (ValidationException) {
            $this->assertSame('under_repair', $mould->fresh()->status);
        }
        $this->finish($repairer, $second, 'Second');
        Livewire::actingAs($coordinator)->test('production-dashboard')->call('testMouldRepair', $second->id, true);
        $this->assertSame('active', $mould->fresh()->status);
    }

    public function test_new_fault_during_repair_does_not_get_cleared_by_an_older_request(): void
    {
        Notification::fake();
        $coordinator = User::factory()->create(['role' => Role::Coordinator]);
        $operator = User::factory()->create(['role' => Role::Operator]);
        $repairer = User::factory()->create(['role' => Role::Reparation]);
        $mould = $this->mould('warning');
        $machine = Machine::create(['name' => 'Machine', 'type' => 'Test', 'status' => 'active']);
        $product = Product::create(['name' => 'Product', 'code' => 'TEST']);
        $order = Order::factory()->create([
            'machine_id' => $machine->id, 'mould_id' => $mould->id, 'product_id' => $product->id,
            'status' => 'in_progress', 'production_protocol' => [],
        ]);
        $this->createRequest($coordinator, $mould, 'reparation');
        $request = $mould->repairRequests()->firstOrFail();
        $this->assertSame('paused', $order->fresh()->status);

        Livewire::actingAs($operator)->test('production-dashboard')
            ->set('protocolOrderId', $order->id)
            ->set('protocolEntryType', 'mould_issue')
            ->set('protocolEntryNote', 'New crack')
            ->set('protocolCurrentQuantity', 0)
            ->call('addProductionProtocolEntry')->assertHasNoErrors();
        $this->assertSame('under_repair', $mould->fresh()->status);
        Notification::assertSentTo($coordinator, MouldRepairNotification::class, fn ($notification) => $notification->toArray($coordinator)['event'] === 'mould_issue_reported');
        $this->finish($repairer, $request, 'Old fault');
        Livewire::actingAs($operator)->test('production-dashboard')->call('testMouldRepair', $request->id, true);
        $this->assertSame('needs_repair', $mould->fresh()->status);
        $this->assertSame('New crack', $mould->fresh()->problem_description);
        $this->assertSame('paused', $order->fresh()->status);
    }

    public function test_admin_can_create_users_for_new_roles(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::actingAs(User::factory()->create(['role' => Role::Admin]))
            ->test(CreateUser::class)
            ->fillForm(['name' => 'Repair worker', 'email' => 'repair@example.test', 'password' => 'long-secure-test-password', 'role' => 'reparation'])
            ->call('create')->assertHasNoFormErrors();
        $user = User::query()->where('email', 'repair@example.test')->firstOrFail();
        $this->assertSame(Role::Reparation, $user->role);
        $this->assertTrue(Hash::check('long-secure-test-password', $user->password));

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->assertSet('data.password', null)
            ->fillForm(['role' => 'maintenance'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame(Role::Maintenance, $user->fresh()->role);
        $this->assertTrue(Hash::check('long-secure-test-password', $user->fresh()->password));
        Livewire::test(ListUsers::class)->assertSee('maintenance');
    }

    public function test_successful_repair_releases_the_automatic_order_pause(): void
    {
        Notification::fake();
        $coordinator = User::factory()->create(['role' => Role::Coordinator]);
        $worker = User::factory()->create(['role' => Role::Maintenance]);
        $mould = $this->mould('warning');
        $machine = Machine::create(['name' => 'Machine', 'type' => 'Test', 'status' => 'active']);
        $product = Product::create(['name' => 'Product', 'code' => 'TEST']);
        $order = Order::factory()->create([
            'machine_id' => $machine->id, 'mould_id' => $mould->id, 'product_id' => $product->id,
            'status' => 'in_progress', 'production_protocol' => [],
        ]);
        $this->createRequest($coordinator, $mould, 'maintenance');
        $request = $mould->repairRequests()->firstOrFail();
        $this->assertSame('paused', $order->fresh()->status);
        $this->finish($worker, $request, 'Repaired');
        Livewire::actingAs($worker)->test('production-dashboard')
            ->call('testMouldRepair', $request->id, true)->assertHasNoErrors();
        $this->assertSame('active', $mould->fresh()->status);
        $this->assertSame('in_progress', $order->fresh()->status);
    }

    public function test_only_coordinators_can_request_repairs_and_database_notifications_are_delivered(): void
    {
        $operator = User::factory()->create(['role' => Role::Operator]);
        $coordinator = User::factory()->create(['role' => Role::Coordinator]);
        $programmer = User::factory()->create(['role' => Role::Programmer]);
        $repairer = User::factory()->create(['role' => Role::Reparation]);
        $mould = $this->mould('warning');

        Livewire::actingAs($operator)->test('production-dashboard')
            ->set("repairDescriptions.$mould->id", 'Fault')
            ->set("repairPriorities.$mould->id", 1)
            ->set("repairRoles.$mould->id", 'reparation')
            ->call('createMouldRepair', $mould->id)->assertForbidden();
        $this->assertDatabaseCount('mould_repair_requests', 0);

        $this->createRequest($coordinator, $mould, 'reparation');
        $this->assertSame('repair_requested', $programmer->unreadNotifications()->firstOrFail()->data['event']);
        $this->assertSame('repair_requested', $repairer->unreadNotifications()->firstOrFail()->data['event']);
    }

    public function test_mounted_mould_button_opens_only_its_repair_history(): void
    {
        $operator = User::factory()->create(['role' => Role::Operator]);
        $requester = User::factory()->create(['role' => Role::Coordinator]);
        $repairer = User::factory()->create(['role' => Role::Reparation]);
        $mould = $this->mould('active');
        $otherMould = Mould::create(['name' => 'Other mould', 'number' => 'OTHER', 'status' => 'active']);
        $machine = Machine::create(['name' => 'Machine', 'type' => 'Test', 'status' => 'active']);
        $product = Product::create(['name' => 'Product', 'code' => 'TEST']);
        Order::factory()->create([
            'machine_id' => $machine->id, 'mould_id' => $mould->id, 'product_id' => $product->id,
            'status' => 'in_progress', 'production_protocol' => [], 'activated_at' => null,
        ]);
        $request = $mould->repairRequests()->create([
            'requested_by' => $requester->id, 'description' => 'Mounted mould issue',
            'priority' => 1, 'assigned_role' => 'reparation', 'status' => 'completed',
        ]);
        $request->attempts()->create([
            'repaired_by' => $repairer->id, 'assigned_role' => 'reparation', 'description' => 'Mounted mould repair',
            'duration_minutes' => 75, 'repaired_at' => now(), 'test_passed' => true,
            'tested_by' => $operator->id, 'tested_at' => now(), 'test_note' => 'Test passed',
        ]);
        $otherMould->repairRequests()->create([
            'requested_by' => $requester->id, 'description' => 'Other mould issue',
            'priority' => 1, 'assigned_role' => 'reparation', 'status' => 'waiting',
        ]);

        $dashboard = Livewire::actingAs($operator)->test('production-dashboard')
            ->call('toggleMachineRow', $machine->id)
            ->assertSeeHtml('wire:click.stop="openMouldRepairLog('.$mould->id.')"')
            ->call('openMouldRepairLog', $mould->id)
            ->assertSee('Mounted mould issue')
            ->assertSee('Mounted mould repair')
            ->assertSee('Test passed')
            ->assertSee('1 óra 15 perc')
            ->assertDontSee('Other mould issue')
            ->call('closeMouldRepairLog')
            ->assertSet('mouldRepairLogId', null)
            ->assertDontSee('Mounted mould repair');

        $dashboard->call('openMachineDetail', $machine->id)
            ->assertSeeHtml('wire:click.stop="openMouldRepairLog('.$mould->id.')"')
            ->call('openMouldRepairLog', $mould->id)
            ->assertSee('Mounted mould repair')
            ->call('closeMouldRepairLog')
            ->assertSet('detailMachineId', $machine->id);
    }

    public function test_mould_repair_log_has_an_empty_state_and_rejects_missing_moulds(): void
    {
        $operator = User::factory()->create(['role' => Role::Operator]);
        $mould = $this->mould('active');

        $dashboard = Livewire::actingAs($operator)->test('production-dashboard')
            ->call('openMouldRepairLog', $mould->id)
            ->assertSee('Ehhez a szerszámhoz még nincs javítási bejegyzés.')
            ->call('closeMouldRepairLog')
            ->assertDontSee('Ehhez a szerszámhoz még nincs javítási bejegyzés.');

        $this->expectException(ModelNotFoundException::class);
        $dashboard->call('openMouldRepairLog', 999999);
    }

    private function mould(string $status): Mould
    {
        return Mould::create(['name' => 'Mould', 'number' => 'TEST', 'cavity_count' => 1, 'status' => $status, 'problem_description' => 'Problem']);
    }

    private function createRequest(User $coordinator, Mould $mould, string $role): void
    {
        Livewire::actingAs($coordinator)->test('production-dashboard')
            ->set("repairDescriptions.$mould->id", 'Fault description')
            ->set("repairPriorities.$mould->id", 1)
            ->set("repairRoles.$mould->id", $role)
            ->call('createMouldRepair', $mould->id)->assertHasNoErrors();
    }

    private function finish(User $worker, MouldRepairRequest $request, string $description): void
    {
        Livewire::actingAs($worker)->test('production-dashboard')
            ->call('setMouldRepairWorkStatus', $request->id, 'in_progress')
            ->set("repairWorkDescriptions.$request->id", $description)
            ->set("repairHours.$request->id", 0)
            ->set("repairMinutes.$request->id", 20)
            ->call('finishMouldRepair', $request->id)->assertHasNoErrors();
    }
}

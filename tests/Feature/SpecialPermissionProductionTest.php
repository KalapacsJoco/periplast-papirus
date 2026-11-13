<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Models\Machine;
use App\Models\Mould;
use App\Models\Order;
use App\Models\Product;
use App\Models\SpecialPermissionRequest;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class SpecialPermissionProductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_approval_keeps_production_stopped_until_an_operator_or_coordinator_starts_it(): void
    {
        Notification::fake();
        $qualityManager = User::factory()->create(['role' => Role::QualityManager]);
        $admin = User::factory()->create(['role' => Role::Admin]);

        foreach ([Role::Operator, Role::Coordinator] as $role) {
            $starter = User::factory()->create(['role' => $role]);
            $order = $this->runningOrder();
            if ($role === Role::Coordinator) {
                $order->machine->update(['status' => 'setup']);
            }

            Livewire::actingAs($qualityManager)->test('production-dashboard')
                ->set('qualityOrderId', $order->id)
                ->set('qualityStatus', 'special_permission')
                ->set('specialReason', 'Accepted deviation')
                ->set('currentQuantity', 0)
                ->call('saveQualityCheck', true)
                ->assertHasNoErrors();

            $request = $order->specialPermissionRequests()->firstOrFail();
            $this->assertSame('paused', $order->fresh()->status);

            Livewire::actingAs($admin)->test('production-dashboard')
                ->call('decideSpecialPermission', $request->id, true);

            $this->assertSame('approved', $request->fresh()->status);
            $this->assertSame('paused', $order->fresh()->status);
            $this->assertSame($role === Role::Coordinator ? 'setup' : 'active', $order->machine->fresh()->status);

            $component = Livewire::actingAs($starter)->test('production-dashboard');
            $machine = $component->instance()->machines->find($order->machine_id);
            $this->assertSame('stopped', $component->instance()->machineState($machine));
            $component->assertSee('border-2 !border-yellow-500')
                ->assertSeeHtml('[&>td]:!border-yellow-500')
                ->assertSee('Gyártás különleges engedéllyel')
                ->call('openMachineDetail', $machine->id)
                ->assertSee('Gyártás indítása különleges engedéllyel')
                ->call('startSpecialPermissionProduction', $order->id)
                ->assertHasNoErrors();

            $this->assertSame('in_progress', $order->fresh()->status);
            $this->assertSame('active', $order->machine->fresh()->status);
            $this->assertNull($order->fresh()->pause_description);
            $component = Livewire::actingAs($starter)->test('production-dashboard');
            $machine = $component->instance()->machines->find($order->machine_id);
            $this->assertSame('running', $component->instance()->machineState($machine));
            $component->assertSee('border-2 !border-yellow-500');

            $order->update(['status' => 'completed', 'ended_at' => now()]);
            $component = Livewire::actingAs($starter)->test('production-dashboard');
            $this->assertFalse($component->instance()->machines->find($order->machine_id)->productionMetrics()->currentOrder()?->hasApprovedSpecialPermission() ?? false);
        }
    }

    public function test_only_operators_and_coordinators_can_start_and_other_faults_still_block_it(): void
    {
        Notification::fake();
        $order = $this->runningOrder();
        $request = SpecialPermissionRequest::create([
            'product_id' => $order->product_id,
            'order_id' => $order->id,
            'reason' => 'Deviation',
            'status' => 'pending',
        ]);
        $order->pauseForIssue('special:'.$request->id, 'Deviation');

        $operator = User::factory()->create(['role' => Role::Operator]);
        Livewire::actingAs($operator)->test('production-dashboard')
            ->call('startSpecialPermissionProduction', $order->id)
            ->assertStatus(422);

        $admin = User::factory()->create(['role' => Role::Admin]);
        Livewire::actingAs($admin)->test('production-dashboard')
            ->call('decideSpecialPermission', $request->id, true)
            ->call('startSpecialPermissionProduction', $order->id)
            ->assertForbidden();

        config(['services.api_key' => 'test-api-key']);
        $this->withHeader('X-API-Key', 'test-api-key')->postJson('/api/orders/'.$order->id.'/status', [
            'status' => 'in_progress',
            'current_quantity' => 0,
        ])->assertUnprocessable();

        $order->product->moulds()->attach($order->mould_id);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::actingAs($admin)->test(EditOrder::class, ['record' => $order->id])
            ->fillForm(['status' => 'in_progress'])
            ->call('save')
            ->assertHasErrors(['data.status']);

        $programmer = User::factory()->create(['role' => Role::Programmer]);
        Livewire::actingAs($programmer)->test('production-dashboard')
            ->call('activateOrder', $order->id)
            ->assertStatus(422);

        $order->mould->update(['status' => 'needs_repair', 'problem_description' => 'Broken mould']);
        Livewire::actingAs($operator)->test('production-dashboard')
            ->call('startSpecialPermissionProduction', $order->id)
            ->assertStatus(422);
        $order->mould->update(['status' => 'active', 'problem_description' => null]);
        $this->assertSame('paused', $order->fresh()->status);

        Livewire::actingAs($operator)->test('production-dashboard')
            ->call('startSpecialPermissionProduction', $order->id)
            ->assertHasNoErrors();
        $this->assertSame('in_progress', $order->fresh()->status);
    }

    public function test_approval_of_a_quality_issue_does_not_resume_production(): void
    {
        Notification::fake();
        $order = $this->runningOrder();
        $order->update(['production_protocol' => [
            'quality_status' => 'quality_issue',
            'quality_issues' => [['id' => 'issue-1', 'status' => 'open']],
        ]]);
        $order->pauseForIssue('quality:issue-1', 'Quality issue');
        $request = SpecialPermissionRequest::create([
            'product_id' => $order->product_id,
            'order_id' => $order->id,
            'quality_issue_id' => 'issue-1',
            'reason' => 'Deviation',
            'status' => 'pending',
        ]);
        $order->fresh()->pauseForIssue('special:'.$request->id, 'Deviation');

        Livewire::actingAs(User::factory()->create(['role' => Role::Admin]))
            ->test('production-dashboard')
            ->call('decideSpecialPermission', $request->id, true);

        $this->assertSame('paused', $order->fresh()->status);
        $this->assertSame(['special:'.$request->id], $order->fresh()->specialPermissionPauseCauses());
        $this->assertSame('good', $order->fresh()->production_protocol['quality_status']);
    }

    public function test_approval_of_a_request_on_a_manually_paused_order_requires_manual_start(): void
    {
        Notification::fake();
        $order = $this->runningOrder();
        $order->update(['status' => 'paused', 'pause_description' => 'Manual pause']);
        $request = SpecialPermissionRequest::create([
            'product_id' => $order->product_id,
            'order_id' => $order->id,
            'reason' => 'Deviation',
            'status' => 'pending',
        ]);

        Livewire::actingAs(User::factory()->create(['role' => Role::Admin]))
            ->test('production-dashboard')
            ->call('decideSpecialPermission', $request->id, true);

        $this->assertSame('paused', $order->fresh()->status);
        $this->assertSame(['special:'.$request->id], $order->fresh()->specialPermissionPauseCauses());
        Livewire::actingAs(User::factory()->create(['role' => Role::Operator]))
            ->test('production-dashboard')
            ->call('startSpecialPermissionProduction', $order->id)
            ->assertHasNoErrors();
        $this->assertSame('in_progress', $order->fresh()->status);
    }

    private function runningOrder(): Order
    {
        $machine = Machine::query()->create(['name' => 'Test machine', 'type' => 'Test', 'status' => 'active']);
        $mould = Mould::query()->create(['name' => 'Test mould', 'number' => 'TEST', 'cavity_count' => 1, 'status' => 'active']);
        $product = Product::query()->create(['name' => 'Test product', 'code' => fake()->unique()->uuid()]);
        $order = Order::factory()->create([
            'machine_id' => $machine->id,
            'mould_id' => $mould->id,
            'product_id' => $product->id,
            'status' => 'in_progress',
            'activated_at' => now(),
            'task_started_at' => now(),
            'qm_approved_at' => null,
            'production_protocol' => [],
        ]);
        return $order->refresh();
    }
}

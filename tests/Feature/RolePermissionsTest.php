<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Machine;
use App\Models\Mould;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_have_only_the_requested_permissions(): void
    {
        $this->assertSame([Role::PERMISSION_ALL], Role::Admin->permissions());
        $this->assertSame([Role::ACTIVATE_ORDER, Role::PAUSE_ORDER, Role::INTERRUPT_ORDER], Role::Programmer->permissions());
        $this->assertSame([Role::ASSIGN_ORDER, Role::START_MACHINE, Role::STOP_MACHINE, Role::REPORT_EQUIPMENT_WARNING, Role::CHECK_QUALITY], Role::Coordinator->permissions());
        $this->assertSame([Role::START_MACHINE, Role::STOP_MACHINE, Role::REPORT_EQUIPMENT_WARNING, Role::CHECK_COLOR], Role::Operator->permissions());
        $this->assertSame([Role::CHECK_QUALITY, Role::STOP_MACHINE, Role::REPORT_EQUIPMENT_WARNING], Role::QualityManager->permissions());
    }

    public function test_programmer_order_activation_notifies_coordinator_and_all_operators(): void
    {
        $programmer = User::factory()->create(['role' => Role::Programmer]);
        $coordinator = User::factory()->create(['role' => Role::Coordinator]);
        $operators = User::factory()->count(2)->create(['role' => Role::Operator]);
        $order = Order::factory()->create(['status' => 'waiting']);

        Livewire::actingAs($programmer)
            ->test('production-dashboard')
            ->call('activateOrder', $order->id);

        $this->assertSame('in_progress', $order->fresh()->status);
        $this->assertSame(3, $coordinator->unreadNotifications()->count() + $operators->sum(
            fn (User $operator): int => $operator->unreadNotifications()->count()
        ));
        $this->assertDatabaseCount('notifications', 3);
    }

    public function test_programmer_pausing_and_interrupting_orders_notifies_recipients(): void
    {
        $programmer = User::factory()->create(['role' => Role::Programmer]);
        $coordinator = User::factory()->create(['role' => Role::Coordinator]);
        $operator = User::factory()->create(['role' => Role::Operator]);
        $order = Order::factory()->create(['status' => 'in_progress']);

        Livewire::actingAs($programmer)
            ->test('production-dashboard')
            ->call('openOrderModal', $order->id)
            ->set('orderStatus', 'paused')
            ->call('saveOrder');

        $this->assertSame('paused', $order->fresh()->status);
        $this->assertSame('paused', $coordinator->unreadNotifications()->firstOrFail()->data['event']);
        $this->assertSame('paused', $operator->unreadNotifications()->firstOrFail()->data['event']);

        Livewire::actingAs($programmer)
            ->test('production-dashboard')
            ->call('openOrderModal', $order->id)
            ->set('orderStatus', 'interrupted')
            ->set('goodQuantity', 10)
            ->set('badQuantity', 0)
            ->call('saveOrder');

        $this->assertSame('interrupted', $order->fresh()->status);
        $this->assertDatabaseCount('notifications', 4);
    }

    public function test_coordinator_can_assign_an_active_order_to_an_operator(): void
    {
        $coordinator = User::factory()->create(['role' => Role::Coordinator]);
        $operator = User::factory()->create(['role' => Role::Operator]);
        $replacementOperator = User::factory()->create(['role' => Role::Operator]);
        $order = Order::factory()->create(['status' => 'in_progress']);

        Livewire::actingAs($coordinator)
            ->test('production-dashboard')
            ->call('assignOperator', $order->id, (string) $operator->id);

        $this->assertSame($operator->id, $order->fresh()->assigned_operator_id);
        $this->assertSame('assigned', $operator->unreadNotifications()->firstOrFail()->data['event']);

        Livewire::actingAs($coordinator)
            ->test('production-dashboard')
            ->call('assignOperator', $order->id, (string) $replacementOperator->id);

        $this->assertSame($replacementOperator->id, $order->fresh()->assigned_operator_id);
        $this->assertSame('assigned', $replacementOperator->unreadNotifications()->firstOrFail()->data['event']);
    }

    public function test_coordinator_cannot_assign_an_order_before_programmer_activates_it(): void
    {
        $coordinator = User::factory()->create(['role' => Role::Coordinator]);
        $operator = User::factory()->create(['role' => Role::Operator]);
        $order = Order::factory()->create(['status' => 'waiting']);

        try {
            Livewire::actingAs($coordinator)
                ->test('production-dashboard')
                ->call('assignOperator', $order->id, (string) $operator->id);

            $this->fail('A waiting order cannot be assigned.');
        } catch (ModelNotFoundException) {
        }

        $this->assertNull($order->fresh()->assigned_operator_id);
    }

    public function test_coordinator_tasks_only_list_activated_orders_and_orders_page_has_no_assignment_section(): void
    {
        $coordinator = User::factory()->create(['role' => Role::Coordinator]);
        $activatedOrder = Order::factory()->create(['order_number' => 'ORD-ACTIVE', 'status' => 'in_progress']);
        $waitingOrder = Order::factory()->create(['order_number' => 'ORD-WAITING', 'status' => 'waiting']);

        Livewire::actingAs($coordinator)
            ->test('production-dashboard')
            ->call('selectTab', 'tasks')
            ->assertSet('activeTab', 'tasks')
            ->assertSee($activatedOrder->order_number)
            ->assertDontSee($waitingOrder->order_number)
            ->call('selectTab', 'orders')
            ->assertDontSee('Operátori feladatkiosztás');
    }

    public function test_operator_starts_only_an_assigned_active_task_and_machine_enters_setup(): void
    {
        $operator = User::factory()->create(['role' => Role::Operator]);
        $otherOperator = User::factory()->create(['role' => Role::Operator]);
        $order = Order::factory()->create([
            'status' => 'in_progress',
            'assigned_operator_id' => $operator->id,
        ]);

        Livewire::actingAs($operator)
            ->test('production-dashboard')
            ->call('selectTab', 'tasks')
            ->assertSee($order->order_number)
            ->call('startAssignedTask', $order->id)
            ->assertDontSee($order->order_number)
            ->assertSee('Jelenleg nincs hozzád rendelt feladat.');

        $this->assertSame('setup', $order->machine->fresh()->status);
        $this->assertNotNull($order->fresh()->task_started_at);

        try {
            Livewire::actingAs($otherOperator)
                ->test('production-dashboard')
                ->call('startAssignedTask', $order->id);

            $this->fail('An operator cannot start another operator’s task.');
        } catch (ModelNotFoundException) {
        }
    }

    public function test_machine_card_shows_expected_quantity_from_cavities_and_cycle_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-18 12:00:00'));

        try {
            $operator = User::factory()->create(['role' => Role::Operator]);
            $order = Order::factory()->create([
                'status' => 'in_progress',
                'quantity' => 1000,
                'current_quantity' => 30,
                'started_at' => now()->subHours(2),
                'production_protocol' => ['quality_checks' => [
                    ['checked_at' => now()->subSeconds(500)->toIso8601String(), 'quality_status' => 'warning'],
                    ['checked_at' => now()->subSeconds(100)->toIso8601String(), 'quality_status' => 'good'],
                    ['checked_at' => now()->subSeconds(50)->toIso8601String(), 'quality_status' => 'good'],
                ]],
                'mould_id' => Mould::factory()->create(['cavity_count' => 4])->id,
                'product_id' => Product::factory()->create(['planned_cycle_time' => 10000])->id,
            ]);

            $this->assertSame(40, Livewire::actingAs($operator)->test('production-dashboard')->instance()->expectedQuantity($order->load(['mould', 'product'])));

            Livewire::actingAs($operator)
                ->test('production-dashboard')
                ->assertSee('Elméleti mennyiség: 40 db')
                ->assertSee('bg-red-400');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_expected_quantity_is_not_shown_before_first_good_quality_check(): void
    {
        $operator = User::factory()->create(['role' => Role::Operator]);
        $order = Order::factory()->create([
            'status' => 'in_progress',
            'quantity' => 1000,
            'started_at' => now()->subHour(),
            'mould_id' => Mould::factory()->create(['cavity_count' => 4])->id,
            'product_id' => Product::factory()->create(['planned_cycle_time' => 10000])->id,
            'production_protocol' => ['quality_checks' => [
                ['checked_at' => now()->subMinutes(5)->toIso8601String(), 'quality_status' => 'quality_issue'],
            ]],
        ]);

        $component = Livewire::actingAs($operator)->test('production-dashboard');

        $this->assertNull($component->instance()->expectedQuantity($order->load(['mould', 'product'])));
        $component->assertDontSee('Elméleti mennyiség:');
    }

    public function test_quality_check_asks_for_confirmation_when_quantity_is_below_last_or_above_expected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-18 12:00:00'));

        try {
            $qualityManager = User::factory()->create(['role' => Role::QualityManager]);
            $order = Order::factory()->create([
                'status' => 'in_progress',
                'quantity' => 1000,
                'current_quantity' => 30,
                'mould_id' => Mould::factory()->create(['cavity_count' => 4])->id,
                'product_id' => Product::factory()->create(['planned_cycle_time' => 10000])->id,
                'production_protocol' => ['quality_checks' => [
                    ['checked_at' => now()->subSeconds(100)->toIso8601String(), 'quality_status' => 'good'],
                ]],
            ]);

            Livewire::actingAs($qualityManager)
                ->test('production-dashboard')
                ->call('openQualityModal', $order->id)
                ->set('currentQuantity', 20)
                ->call('saveQualityCheck')
                ->assertSet('showQualityModal', true)
                ->assertSee('kisebb, mint a legutóbb bevitt (30 db)')
                ->assertSee('Biztosan módosítani szeretnéd')
                ->call('saveQualityCheck', true)
                ->assertSet('showQualityModal', false);

            $this->assertSame(20, $order->fresh()->current_quantity);

            Livewire::actingAs($qualityManager)
                ->test('production-dashboard')
                ->call('openQualityModal', $order->id)
                ->set('currentQuantity', 41)
                ->call('saveQualityCheck')
                ->assertSet('showQualityModal', true)
                ->assertSee('nagyobb, mint az elméleti darabszám (40 db)');

            $this->assertSame(20, $order->fresh()->current_quantity);

            Livewire::actingAs($qualityManager)
                ->test('production-dashboard')
                ->call('openQualityModal', $order->id)
                ->set('currentQuantity', 40)
                ->call('saveQualityCheck')
                ->assertSet('showQualityModal', false);

            $this->assertSame(40, $order->fresh()->current_quantity);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_operator_task_list_hides_tasks_that_have_already_started(): void
    {
        $operator = User::factory()->create(['role' => Role::Operator]);
        $startedOrder = Order::factory()->create([
            'order_number' => 'ORD-STARTED',
            'status' => 'in_progress',
            'assigned_operator_id' => $operator->id,
            'task_started_at' => now(),
        ]);
        $queuedOrder = Order::factory()->create([
            'order_number' => 'ORD-QUEUED',
            'status' => 'in_progress',
            'assigned_operator_id' => $operator->id,
        ]);

        Livewire::actingAs($operator)
            ->test('production-dashboard')
            ->call('selectTab', 'tasks')
            ->assertDontSee($startedOrder->order_number)
            ->assertSee($queuedOrder->order_number);
    }

    public function test_all_roles_can_view_orders_but_only_programmer_can_activate_them(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'ORD-VISIBLE',
            'status' => 'waiting',
        ]);
        $coordinator = User::factory()->create(['role' => Role::Coordinator]);
        $operator = User::factory()->create(['role' => Role::Operator]);
        $qualityManager = User::factory()->create(['role' => Role::QualityManager]);

        foreach ([$coordinator, $operator, $qualityManager] as $user) {
            Livewire::actingAs($user)
                ->test('production-dashboard')
                ->call('selectTab', 'orders')
                ->assertSet('activeTab', 'orders')
                ->assertSee($user->name)
                ->assertSee('ORD-VISIBLE')
                ->assertDontSee('activateOrder');
        }

        $programmer = User::factory()->create(['role' => Role::Programmer]);

        Livewire::actingAs($programmer)
            ->test('production-dashboard')
            ->call('selectTab', 'orders')
            ->assertSee('ORD-VISIBLE')
            ->assertSee('activateOrder');

        $this->assertSame('waiting', $order->fresh()->status);
    }

    public function test_operator_color_check_only_accepts_color_problems(): void
    {
        $operator = User::factory()->create(['role' => Role::Operator]);
        $order = Order::factory()->create(['status' => 'in_progress']);

        Livewire::actingAs($operator)
            ->test('production-dashboard')
            ->call('openQualityModal', $order->id)
            ->set('qualityStatus', 'quality_issue')
            ->set('qualityProblemType', 'surface_defect')
            ->set('qualityProblemNote', 'Eltérés')
            ->call('saveQualityCheck')
            ->assertHasErrors('qualityProblemType')
            ->set('qualityProblemType', 'color_issue')
            ->call('saveQualityCheck');

        $this->assertSame('color', $order->fresh()->production_protocol['quality_checks'][0]['check_type']);
    }

    public function test_coordinator_and_operator_can_set_machine_statuses(): void
    {
        foreach ([Role::Coordinator, Role::Operator] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $machine = Machine::factory()->create(['status' => 'stopped']);

            foreach (['active', 'warning', 'stopped'] as $status) {
                Livewire::actingAs($user)
                    ->test('production-dashboard')
                    ->call('openMachineModal', $machine->id)
                    ->set('status', $status)
                    ->set('problemDescription', $status === 'warning' ? 'Scheduled maintenance' : '')
                    ->call('saveMachineStatus')
                    ->assertHasNoErrors();

                $this->assertSame($status, $machine->fresh()->status);
            }
        }
    }

    public function test_machine_card_displays_the_saved_machine_status(): void
    {
        $operator = User::factory()->create(['role' => Role::Operator]);
        $machine = Machine::factory()->create(['name' => 'Status test machine', 'status' => 'active']);

        Livewire::actingAs($operator)
            ->test('production-dashboard')
            ->assertSee('Aktív')
            ->call('openMachineModal', $machine->id)
            ->set('status', 'warning')
            ->set('problemDescription', 'Scheduled inspection')
            ->call('saveMachineStatus')
            ->assertSee('Figyelmeztetés')
            ->assertSee('bg-amber-100')
            ->assertSee('Scheduled inspection');
    }

    public function test_machine_status_changes_are_signed_in_the_active_order_protocol(): void
    {
        $operator = User::factory()->create(['name' => 'Signed Operator', 'role' => Role::Operator]);
        $order = Order::factory()->create(['status' => 'in_progress']);

        Livewire::actingAs($operator)
            ->test('production-dashboard')
            ->call('openMachineModal', $order->machine_id)
            ->set('status', 'warning')
            ->set('problemDescription', 'Pressure drift')
            ->call('saveMachineStatus');

        $entry = $order->fresh()->production_protocol['entries'][0];
        $this->assertSame('machine_status', $entry['entry_type']);
        $this->assertSame('Signed Operator', $entry['signed_by']);
        $this->assertSame($operator->id, $entry['signed_by_user_id']);
        $this->assertStringContainsString('Pressure drift', $entry['note']);
        $this->assertNotEmpty($entry['timestamp']);
    }

    public function test_manual_production_protocol_entries_include_timestamp_and_signature(): void
    {
        $operator = User::factory()->create(['name' => 'Protocol Operator', 'role' => Role::Operator]);
        $order = Order::factory()->create(['status' => 'in_progress']);

        Livewire::actingAs($operator)
            ->test('production-dashboard')
            ->call('openProductionProtocol', $order->id)
            ->set('protocolEntryType', 'parameter_change')
            ->set('protocolEntryNote', 'Injection pressure changed to 850 bar')
            ->call('addProductionProtocolEntry')
            ->assertSee('Injection pressure changed to 850 bar')
            ->assertSee('Protocol Operator')
            ->assertSee('border-amber-500')
            ->assertSee('⚙');

        $entry = $order->fresh()->production_protocol['entries'][0];
        $this->assertSame('parameter_change', $entry['entry_type']);
        $this->assertSame('Protocol Operator', $entry['signed_by']);
        $this->assertSame($operator->id, $entry['signed_by_user_id']);
        $this->assertNotEmpty($entry['timestamp']);
    }

    public function test_quality_manager_can_warn_or_stop_but_not_start_a_machine(): void
    {
        $qualityManager = User::factory()->create(['role' => Role::QualityManager]);
        $machine = Machine::factory()->create(['status' => 'stopped']);

        Livewire::actingAs($qualityManager)
            ->test('production-dashboard')
            ->call('openMachineModal', $machine->id)
            ->set('status', 'active')
            ->call('saveMachineStatus')
            ->assertForbidden();

        $this->assertSame('stopped', $machine->fresh()->status);

        foreach (['warning', 'stopped'] as $status) {
            Livewire::actingAs($qualityManager)
                ->test('production-dashboard')
                ->call('openMachineModal', $machine->id)
                ->set('status', $status)
                ->set('problemDescription', $status === 'warning' ? 'Maintenance needed' : '')
                ->call('saveMachineStatus')
                ->assertHasNoErrors();

            $this->assertSame($status, $machine->fresh()->status);
        }
    }

    public function test_coordinator_operator_and_quality_manager_can_add_mould_warnings_only(): void
    {
        foreach ([Role::Coordinator, Role::Operator, Role::QualityManager] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $mould = Mould::factory()->create(['status' => 'active']);

            Livewire::actingAs($user)
                ->test('production-dashboard')
                ->call('openMouldModal', $mould->id)
                ->set('status', 'warning')
                ->set('problemDescription', 'Tool needs inspection')
                ->call('saveMouldStatus')
                ->assertHasNoErrors();

            $this->assertSame('warning', $mould->fresh()->status);
            $this->assertSame('Tool needs inspection', $mould->fresh()->problem_description);
        }

        $operator = User::factory()->create(['role' => Role::Operator]);
        $mould = Mould::factory()->create(['status' => 'active']);

        Livewire::actingAs($operator)
            ->test('production-dashboard')
            ->call('openMouldModal', $mould->id)
            ->set('status', 'active')
            ->call('saveMouldStatus')
            ->assertForbidden();

        $this->assertSame('active', $mould->fresh()->status);
    }

    public function test_programmer_cannot_complete_an_order(): void
    {
        $programmer = User::factory()->create(['role' => Role::Programmer]);
        $order = Order::factory()->create(['status' => 'in_progress']);

        Livewire::actingAs($programmer)
            ->test('production-dashboard')
            ->call('openOrderModal', $order->id)
            ->set('orderStatus', 'completed')
            ->set('goodQuantity', 10)
            ->set('badQuantity', 0)
            ->call('saveOrder')
            ->assertHasErrors('orderStatus');

        $this->assertSame('in_progress', $order->fresh()->status);
    }

    public function test_only_admin_can_access_the_filament_admin_panel(): void
    {
        $coordinator = User::factory()->create(['role' => Role::Coordinator]);

        $this->actingAs($coordinator)
            ->get('/admin')
            ->assertForbidden();
    }
}

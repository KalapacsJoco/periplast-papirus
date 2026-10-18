<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Machine;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_have_only_the_requested_permissions(): void
    {
        $this->assertSame([Role::PERMISSION_ALL], Role::Admin->permissions());
        $this->assertSame([Role::ACTIVATE_ORDER, Role::PAUSE_ORDER, Role::INTERRUPT_ORDER], Role::Programmer->permissions());
        $this->assertSame([Role::ASSIGN_ORDER, Role::START_MACHINE, Role::STOP_MACHINE, Role::CHECK_QUALITY], Role::Coordinator->permissions());
        $this->assertSame([Role::START_MACHINE, Role::STOP_MACHINE, Role::CHECK_COLOR], Role::Operator->permissions());
        $this->assertSame([Role::CHECK_QUALITY, Role::STOP_MACHINE], Role::QualityManager->permissions());
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
        $order = Order::factory()->create(['status' => 'in_progress']);

        Livewire::actingAs($coordinator)
            ->test('production-dashboard')
            ->call('assignOperator', $order->id, (string) $operator->id);

        $this->assertSame($operator->id, $order->fresh()->assigned_operator_id);
        $this->assertSame('assigned', $operator->unreadNotifications()->firstOrFail()->data['event']);
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

    public function test_quality_manager_cannot_start_a_machine(): void
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

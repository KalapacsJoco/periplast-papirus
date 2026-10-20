<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Machine;
use App\Models\Mould;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_machine_status_filter_includes_mould_and_product_warnings(): void
    {
        $user = User::factory()->create(['role' => Role::Operator]);
        $active = Machine::factory()->create(['name' => 'M-ACTIVE', 'status' => 'active']);
        Machine::factory()->create(['name' => 'M-STOPPED', 'status' => 'stopped']);
        Machine::factory()->create(['name' => 'M-WARN', 'status' => 'warning']);
        $mould = Mould::factory()->create(['status' => 'warning']);
        Order::factory()->create(['machine_id' => $active->id, 'mould_id' => $mould->id, 'status' => 'in_progress']);

        Livewire::actingAs($user)->test('production-dashboard')
            ->assertSeeHtml('wire:model.live="machineFilter"')
            ->assertSee('Aktív gépek')
            ->set('machineFilter', 'warning')
            ->assertSee('M-WARN')
            ->assertSee('M-ACTIVE')
            ->assertDontSee('M-STOPPED')
            ->set('machineFilter', 'stopped')
            ->assertSee('M-STOPPED')
            ->assertDontSee('M-WARN');
    }

    public function test_machine_table_shows_the_requested_columns_and_opens_machine_details_on_click(): void
    {
        $user = User::factory()->create(['role' => Role::Operator]);
        $machine = Machine::factory()->create(['name' => 'M-TABLE-01']);
        $order = Order::factory()->create([
            'machine_id' => $machine->id,
            'status' => 'in_progress',
            'order_number' => 'ORD-TABLE-01',
        ]);

        Livewire::actingAs($user)->test('production-dashboard')
            ->assertSee('Gép száma')
            ->assertSee('Éppen futó termék')
            ->assertSee('Tegnapi darabszám azonos időben')
            ->assertSee('Napi festékfogyasztás')
            ->assertSee('M-TABLE-01')
            ->assertSee('aria-label="Minőségellenőrzés szükséges"', false)
            ->call('openMachineDetail', $machine->id)
            ->assertSee('ORD-TABLE-01')
            ->assertSee('Minőség:');
    }

    public function test_machine_detail_offers_mould_and_quality_actions_for_an_activated_order(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $machine = Machine::factory()->create();
        Order::factory()->create([
            'machine_id' => $machine->id,
            'status' => 'in_progress',
            'activated_at' => now(),
            'task_started_at' => null,
        ]);

        Livewire::actingAs($admin)->test('production-dashboard')
            ->call('openMachineDetail', $machine->id)
            ->assertDontSee('Gép státuszának módosítása');
    }

    public function test_machine_state_distinguishes_idle_stopped_and_setup_machines(): void
    {
        $user = User::factory()->create(['role' => Role::Operator]);
        $idle = Machine::factory()->create(['status' => 'active']);
        $running = Machine::factory()->create(['status' => 'active']);
        $stoppedByStatus = Machine::factory()->create(['status' => 'stopped']);
        $stoppedByMould = Machine::factory()->create(['status' => 'active']);
        $stoppedByQuality = Machine::factory()->create(['status' => 'active']);
        $setup = Machine::factory()->create(['status' => 'setup']);

        Order::factory()->create(['machine_id' => $running->id, 'status' => 'in_progress', 'mould_id' => Mould::factory()->create(['status' => 'active'])->id, 'production_protocol' => null]);
        Order::factory()->create([
            'machine_id' => $stoppedByMould->id,
            'status' => 'paused',
            'mould_id' => Mould::factory()->create(['status' => 'needs_repair'])->id,
        ]);
        Order::factory()->create([
            'machine_id' => $stoppedByQuality->id,
            'status' => 'paused',
            'production_protocol' => ['quality_status' => 'quality_issue'],
        ]);

        $dashboard = Livewire::actingAs($user)->test('production-dashboard');
        $component = $dashboard->instance();
        $machines = $component->machines->keyBy('id');

        $this->assertSame('idle', $component->machineState($machines[$idle->id]));
        $this->assertSame('running', $component->machineState($machines[$running->id]));
        $this->assertSame('stopped', $component->machineState($machines[$stoppedByStatus->id]));
        $this->assertSame('stopped', $component->machineState($machines[$stoppedByMould->id]));
        $this->assertSame('stopped', $component->machineState($machines[$stoppedByQuality->id]));
        $this->assertSame('setup', $component->machineState($machines[$setup->id]));

        $dashboard->assertSeeHtml('border-l-blue-500')->assertSeeHtml('border-l-red-500')->assertSeeHtml('border-l-slate-300');
    }

    public function test_help_tab_is_visible_to_everyone_in_both_languages(): void
    {
        $user = User::factory()->create(['role' => Role::Operator]);

        Livewire::actingAs($user)->test('production-dashboard')
            ->call('selectTab', 'help')
            ->assertSet('activeTab', 'help')
            ->assertSee('Egy megrendelő útja')
            ->call('selectLanguage', 'de')
            ->assertSee('Der Weg eines Auftrags');
    }

    public function test_production_time_calculator_loads_product_data_and_computes_duration(): void
    {
        $user = User::factory()->create(['role' => Role::Operator]);
        $mould = Mould::factory()->create(['cavity_count' => 4]);
        $product = Product::factory()->create(['planned_cycle_time' => 30000]);
        $product->moulds()->attach($mould);

        Livewire::actingAs($user)->test('production-dashboard')
            ->call('selectTab', 'calculators')
            ->call('openCalculator')
            ->set('calcProductId', $product->id)
            ->assertSet('calcCavities', 4)
            ->assertSet('calcCycleSeconds', 30.0)
            ->set('calcQuantity', 10000)
            ->assertSee('0 nap, 20 óra, 50 perc')
            ->set('calcCavities', 2)
            ->assertSee('1 nap, 17 óra, 40 perc');
    }

    public function test_quality_issue_pauses_production_and_persists_the_quality_status(): void
    {
        $qualityManager = User::factory()->create(['role' => Role::QualityManager]);
        $order = Order::factory()->create([
            'status' => 'in_progress',
            'quantity' => 100,
            'current_quantity' => 25,
        ]);

        Livewire::actingAs($qualityManager)->test('production-dashboard')
            ->call('openQualityModal', $order->id)
            ->set('qualityStatus', 'quality_issue')
            ->set('currentQuantity', 25)
            ->set('qualityProblemType', 'surface_defect')
            ->set('qualityProblemNote', 'Repedés a termék felületén.')
            ->call('saveQualityCheck')
            ->assertSet('showQualityModal', false)
            ->assertSee('a gyártás automatikusan leállítva');

        $order->refresh();

        $this->assertSame('paused', $order->status);
        $this->assertSame('quality_issue', $order->production_protocol['quality_status']);
        $this->assertSame('Repedés a termék felületén.', $order->production_protocol['quality_checks'][0]['problem_note']);
    }

    public function test_quality_warning_is_shown_on_the_machine_card_without_pausing_production(): void
    {
        $qualityManager = User::factory()->create(['role' => Role::QualityManager]);
        $order = Order::factory()->create([
            'status' => 'in_progress',
            'quantity' => 100,
            'current_quantity' => 25,
        ]);

        Livewire::actingAs($qualityManager)->test('production-dashboard')
            ->call('openQualityModal', $order->id)
            ->set('qualityStatus', 'warning')
            ->set('currentQuantity', 25)
            ->set('qualityProblemType', 'surface_defect')
            ->set('qualityProblemNote', 'Enyhe felületi eltérés.')
            ->call('saveQualityCheck')
            ->call('openMachineDetail', $order->machine_id)
            ->call('openProductionProtocol', $order->id)
            ->assertSee('Minőség: Figyelmeztetés')
            ->assertSee('bg-amber-100')
            ->assertSee('border-amber-500');

        $order->refresh();

        $this->assertSame('in_progress', $order->status);
        $this->assertSame('warning', $order->production_protocol['quality_status']);
    }

    public function test_quality_checks_are_signed_and_missing_checks_warn_immediately(): void
    {
        $checkedAt = Carbon::parse('2026-10-18 14:25:38');
        Carbon::setTestNow($checkedAt);

        try {
            $qualityManager = User::factory()->create(['name' => 'Quality Signature', 'role' => Role::QualityManager]);
            $order = Order::factory()->create([
                'status' => 'in_progress',
                'machine_id' => Machine::factory()->create(['name' => 'Quality Timer Machine'])->id,
            ]);

            Livewire::actingAs($qualityManager)->test('production-dashboard')
                ->call('openMachineDetail', $order->machine_id)
                ->assertSee('Még nem történt minőségellenőrzés')
                ->assertSee('Minőségellenőrzés szükséges')
                ->call('openQualityModal', $order->id)
                ->set('currentQuantity', 12)
                ->call('saveQualityCheck')
                ->call('openProductionProtocol', $order->id)
                ->assertSee('Utolsó minőségellenőrzés: '.$checkedAt->copy()->timezone(config('app.display_timezone'))->format('Y-m-d H:i:s'))
                ->assertDontSee('Minőségellenőrzés szükséges')
                ->assertSee('border-emerald-500')
                ->assertSee('✓');

            $check = $order->fresh()->production_protocol['quality_checks'][0];
            $entry = $order->fresh()->production_protocol['entries'][0];

            $this->assertSame($checkedAt->toIso8601String(), $check['checked_at']);
            $this->assertSame('Quality Signature', $check['signed_by']);
            $this->assertSame($qualityManager->id, $check['signed_by_user_id']);
            $this->assertSame('Quality Signature', $entry['signed_by']);
            $this->assertSame($qualityManager->id, $entry['signed_by_user_id']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_machine_card_warns_when_quality_check_is_older_than_three_hours(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-18 14:30:00'));

        try {
            $qualityManager = User::factory()->create(['role' => Role::QualityManager]);
            $order = Order::factory()->create([
                'status' => 'in_progress',
                'production_protocol' => [
                    'last_quality_check_at' => now()->subHours(3)->subSecond()->toIso8601String(),
                ],
            ]);

            Livewire::actingAs($qualityManager)->test('production-dashboard')
                ->call('openMachineDetail', $order->machine_id)
                ->assertSee('Utolsó minőségellenőrzés:')
                ->assertSee('Minőségellenőrzés szükséges');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_api_rejects_requests_without_a_valid_key(): void
    {
        config(['services.api_key' => 'secret-key']);

        $this->getJson('/api/orders')->assertUnauthorized();
        $this->getJson('/api/orders', ['X-API-Key' => 'wrong'])->assertUnauthorized();
        $this->getJson('/api/orders', ['X-API-Key' => 'secret-key'])->assertOk();
        $this->getJson('/api/orders', ['Authorization' => 'Bearer secret-key'])->assertOk();

        config(['services.api_key' => null]);

        $this->getJson('/api/orders', ['X-API-Key' => ''])->assertUnauthorized();
    }

    public function test_api_machine_status_and_parameter_changes_are_signed_in_order_protocol(): void
    {
        config(['services.api_key' => 'secret-key']);
        $this->withHeader('X-API-Key', 'secret-key');
        $order = Order::factory()->create(['status' => 'in_progress']);

        $this->postJson('/api/machines/'.$order->machine_id.'/status', [
            'status' => 'warning',
            'problem_description' => 'Pressure fluctuation',
        ])->assertOk();

        $machineStatusEntry = $order->fresh()->production_protocol['entries'][0];
        $this->assertSame('machine_status', $machineStatusEntry['entry_type']);
        $this->assertSame('API', $machineStatusEntry['signed_by']);

        $this->postJson('/api/orders/'.$order->id.'/protocol', [
            'cycle_time' => 24,
        ])->assertOk();

        $parameterEntry = $order->fresh()->production_protocol['entries'][1];
        $this->assertSame('parameter_change', $parameterEntry['entry_type']);
        $this->assertSame('API', $parameterEntry['signed_by']);
        $this->assertSame(24, $parameterEntry['meta']['after']['cycle_time']);
    }

    public function test_dashboard_can_be_switched_to_german(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        Livewire::actingAs($admin)->test('production-dashboard')
            ->call('selectLanguage', 'de')
            ->assertSet('locale', 'de')
            ->assertSee('Maschinen')
            ->assertSee('Werkzeuge')
            ->assertSee('Produkte');
    }
}

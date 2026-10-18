<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Machine;
use App\Models\Order;
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
                ->assertSee('Még nem történt minőségellenőrzés')
                ->assertSee('Minőségellenőrzés szükséges')
                ->call('openQualityModal', $order->id)
                ->set('currentQuantity', 12)
                ->call('saveQualityCheck')
                ->call('openProductionProtocol', $order->id)
                ->assertSee('Utolsó minőségellenőrzés: '.$checkedAt->format('Y-m-d H:i:s P'))
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
            Order::factory()->create([
                'status' => 'in_progress',
                'production_protocol' => [
                    'last_quality_check_at' => now()->subHours(3)->subSecond()->toIso8601String(),
                ],
            ]);

            Livewire::actingAs($qualityManager)->test('production-dashboard')
                ->assertSee('Utolsó minőségellenőrzés:')
                ->assertSee('Minőségellenőrzés szükséges');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_api_machine_status_and_parameter_changes_are_signed_in_order_protocol(): void
    {
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

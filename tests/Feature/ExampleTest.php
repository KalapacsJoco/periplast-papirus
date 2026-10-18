<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('Minőség: Figyelmeztetés')
            ->assertSee('bg-amber-100');

        $order->refresh();

        $this->assertSame('in_progress', $order->status);
        $this->assertSame('warning', $order->production_protocol['quality_status']);
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

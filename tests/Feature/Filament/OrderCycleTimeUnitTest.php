<?php

namespace Tests\Feature\Filament;

use App\Enums\Role;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Models\Machine;
use App\Models\Mould;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderCycleTimeUnitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::actingAs(User::factory()->create(['role' => Role::Admin]));
    }

    public function test_order_cycle_time_is_entered_in_seconds_and_stored_in_milliseconds(): void
    {
        $product = Product::factory()->create();
        $mould = $this->createMould();
        $product->moulds()->attach($mould);
        $machine = $this->createMachine();

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'order_number' => 'ORD-SECONDS',
                'date' => now()->toDateString(),
                'product_id' => $product->id,
                'mould_id' => $mould->id,
                'machine_id' => $machine->id,
                'quantity' => 100,
                'cycle_time' => '12.345',
                'net_weight' => 100,
                'gross_weight' => 105,
                'status' => 'waiting',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = Order::query()->where('order_number', 'ORD-SECONDS')->firstOrFail();
        $this->assertSame(12345, $order->cycle_time);
    }

    public function test_existing_cycle_time_is_shown_in_seconds_when_editing_an_order(): void
    {
        $product = Product::factory()->create();
        $mould = $this->createMould();
        $product->moulds()->attach($mould);
        $machine = $this->createMachine();
        $order = Order::factory()->create([
            'cycle_time' => 12345,
            'machine_id' => $machine->id,
            'mould_id' => $mould->id,
            'product_id' => $product->id,
        ]);

        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->assertFormSet(['cycle_time' => 12.345]);
    }

    private function createMould(): Mould
    {
        return Mould::query()->create([
            'name' => 'Teszt szerszám',
            'number' => 'M-100',
            'cavity_count' => 2,
            'open_mm' => 250,
            'status' => 'active',
        ]);
    }

    private function createMachine(): Machine
    {
        return Machine::query()->create([
            'name' => 'Teszt gép',
            'type' => 'Teszt',
            'screw_diameter_cm' => 3,
            'status' => 'active',
        ]);
    }
}

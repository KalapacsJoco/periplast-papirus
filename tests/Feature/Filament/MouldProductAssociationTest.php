<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Moulds\Pages\CreateMould;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Enums\Role;
use App\Models\Mould;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MouldProductAssociationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mould_can_be_created_without_a_product_then_assigned_to_a_product(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::actingAs(User::factory()->create(['role' => Role::Admin]));

        Livewire::test(CreateMould::class)
            ->fillForm([
                'name' => 'Teszt szerszám',
                'number' => 'M-100',
                'cavity_count' => 2,
                'open_mm' => 250,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $mould = Mould::query()->where('number', 'M-100')->firstOrFail();
        $this->assertCount(0, $mould->products);

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Teszt termék',
                'code' => 'P-100',
                'moulds' => [$mould->id],
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::query()->where('code', 'P-100')->firstOrFail();
        $this->assertTrue($product->moulds->contains($mould));
    }
}

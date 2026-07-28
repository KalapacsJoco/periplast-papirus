<?php

namespace Database\Factories;

use App\Models\Color;
use App\Models\Material;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    // Define the model's default state
    public function definition(): array
    {
        return [
            'name' => fake()->word() . ' ' . fake()->word(),
            'code' => 'PRD-' . str_pad(fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'customer_code' => 'CUST-' . str_pad(fake()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'material_id' => Material::factory(),
            'color_id' => Color::factory(),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}


<?php

namespace Database\Factories;

use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    // Define the model's default state
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word() . ' Material',
            'code' => 'MAT-' . str_pad(fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}

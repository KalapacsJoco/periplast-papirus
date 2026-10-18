<?php

namespace Database\Factories;

use App\Models\Color;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Color>
 */
class ColorFactory extends Factory
{
    // Define the model's default state
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->colorName(),
            'code' => 'CLR-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}

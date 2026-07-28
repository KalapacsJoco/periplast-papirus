<?php

namespace Database\Factories;

use App\Models\Insert;
use App\Models\Mould;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Insert>
 */
class InsertFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mould_id'    => Mould::factory(),
            'product_id'  => null, // a seederben a szülő mould product_id-jával töltjük
            'name'        => fake()->randomElement(['A betet', 'B betet', 'C betet', '1. betet', '2. betet', '3. betet']),
            'number'      => strtoupper(fake()->bothify('B-###')),
            'description' => fake()->optional(0.5)->sentence(),
            'status'      => fake()->randomElement([
                'active', 'active',
                'standby',
                'warning',
                'needs_repair',
                'under_repair',
            ]),
        ];
    }
}

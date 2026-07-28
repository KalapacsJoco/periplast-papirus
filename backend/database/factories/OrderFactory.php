<?php

namespace Database\Factories;

use App\Models\Machine;
use App\Models\Mould;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    // Define the model's default state
    public function definition(): array
    {
        $netWeight = fake()->numberBetween(100, 5000);
        $wastePercentage = fake()->numberBetween(5, 20);
        $grossWeight = $netWeight + ($netWeight * $wastePercentage / 100);

        return [
            'order_number' => 'ORD-' . now()->format('Y') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'machine_id' => Machine::factory(),
            'mould_id' => Mould::factory(),
            'product_id' => Product::factory(),
            'cycle_time' => fake()->numberBetween(10000, 120000),
            'net_weight' => $netWeight,
            'gross_weight' => round($grossWeight, 3),
            'color_percentage' => fake()->numberBetween(0, 15) / 100,
            'additives_percentages' => $this->generateAdditivesPercentages(),
            'status' => fake()->randomElement(['waiting', 'in_progress', 'paused', 'completed']),
            'pause_description' => fake()->boolean(20) ? fake()->sentence() : null,
        ];
    }

    // Generate random additives percentages array
    private function generateAdditivesPercentages(): array
    {
        if (fake()->boolean(60)) {
            return [];
        }

        $additives = [];
        $count = fake()->numberBetween(1, 3);

        for ($i = 0; $i < $count; $i++) {
            $additives[] = [
                'additive_id' => fake()->numberBetween(1, 5),
                'percentage' => round(fake()->numberBetween(1, 10) + fake()->randomFloat(2, 0, 0.99), 2),
            ];
        }

        return $additives;
    }
}

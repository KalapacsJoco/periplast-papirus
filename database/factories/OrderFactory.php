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

        $cycleTime = fake()->numberBetween(10, 20);
        $colorPercentage = fake()->numberBetween(0, 15) / 100;

        return [
            'order_number' => 'ORD-' . now()->format('Y') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'machine_id' => Machine::factory(),
            'mould_id' => Mould::factory(),
            'product_id' => Product::factory(),
            'date' => fake()->dateTimeBetween('-5 days', '+10 days')->format('Y-m-d'),
            'customer' => fake()->company(),
            'quantity' => fake()->numberBetween(50, 300),
            'current_quantity' => 0,
            'started_at' => null,
            'cycle_time' => $cycleTime,
            'net_weight' => $netWeight,
            'gross_weight' => round($grossWeight, 3),
            'color_percentage' => $colorPercentage,
            'measured_cycle_time' => $cycleTime,
            'measured_net_weight' => $netWeight,
            'measured_gross_weight' => round($grossWeight, 3),
            'measured_color_percentage' => $colorPercentage,
            'measured_at' => now(),
            'additives_percentages' => $this->generateAdditivesPercentages(),
            'status' => fake()->randomElement(['waiting', 'in_progress', 'paused', 'completed']),
            'pause_description' => fake()->boolean(20) ? fake()->sentence() : null,
            'description' => fake()->optional()->sentence(),
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

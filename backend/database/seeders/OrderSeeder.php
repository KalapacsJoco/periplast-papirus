<?php

namespace Database\Seeders;

use App\Models\Additive;
use App\Models\Machine;
use App\Models\Mould;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::query()->delete();

        $machines = Machine::query()->with('moulds.products.additives')->get();

        foreach ($machines as $index => $machine) {
            $moulds = $this->resolveMachineMoulds($machine);

            if ($moulds->isEmpty()) {
                continue;
            }

            $statuses = ['in_progress', 'waiting', 'waiting'];

            foreach ($statuses as $orderOffset => $status) {
                $mould = $moulds[$orderOffset % $moulds->count()];
                $product = $mould->products->random() ?? Product::query()->with('additives')->inRandomOrder()->first();

                if (! $product) {
                    continue;
                }

                $netWeight = fake()->numberBetween(80, 3500);
                $wastePercentage = fake()->numberBetween(3, 18);
                $grossWeight = $netWeight + ($netWeight * $wastePercentage / 100);

                Order::create([
                    'order_number' => sprintf('ORD-%s-%02d-%02d', now()->format('Y'), $index + 1, $orderOffset + 1),
                    'machine_id' => $machine->id,
                    'mould_id' => $mould->id,
                    'product_id' => $product->id,
                    'date' => now()->addDays($orderOffset)->toDateString(),
                    'customer' => sprintf('Customer %02d', $index + 1),
                    'quantity' => fake()->numberBetween(1000, 25000),
                    'cycle_time' => fake()->numberBetween(12000, 85000),
                    'net_weight' => $netWeight,
                    'gross_weight' => round($grossWeight, 3),
                    'color_percentage' => $product->color_id ? round(fake()->randomFloat(2, 0.2, 6), 2) : null,
                    'additives_percentages' => $this->buildAdditivesPercentages($product),
                    'status' => $status,
                    'pause_description' => null,
                    'description' => sprintf('Seeded order %d for %s', $orderOffset + 1, $machine->name),
                ]);
            }
        }
    }

    private function resolveMachineMoulds(Machine $machine): Collection
    {
        if ($machine->moulds->isNotEmpty()) {
            return $machine->moulds;
        }

        $fallbackMoulds = Mould::query()
            ->with('products.additives')
            ->inRandomOrder()
            ->limit(3)
            ->get();

        if ($fallbackMoulds->isEmpty()) {
            return $fallbackMoulds;
        }

        $machine->moulds()->syncWithoutDetaching($fallbackMoulds->pluck('id')->all());

        return $fallbackMoulds;
    }

    private function buildAdditivesPercentages(Product $product): array
    {
        if ($product->additives->isEmpty()) {
            return [];
        }

        return $product->additives
            ->take(fake()->numberBetween(1, $product->additives->count()))
            ->map(fn (Additive $additive): array => [
                'additive_id' => $additive->id,
                'percentage' => round(fake()->randomFloat(2, 0.1, 5), 2),
            ])
            ->values()
            ->all();
    }
}

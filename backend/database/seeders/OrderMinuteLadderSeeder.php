<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OrderMinuteLadderSeeder extends Seeder
{
    /**
     * Set first 20 machine orders for minute-ladder testing.
     */
    public function run(): void
    {
        $machines = Machine::query()
            ->orderBy('id')
            ->take(20)
            ->get();

        $now = Carbon::now();

        foreach ($machines as $index => $machine) {
            $order = Order::query()
                ->where('machine_id', $machine->id)
                ->where('status', 'in_progress')
                ->orderBy('id')
                ->first();

            if (! $order) {
                $order = Order::query()
                    ->where('machine_id', $machine->id)
                    ->orderBy('id')
                    ->first();
            }

            if (! $order) {
                continue;
            }

            $order->update([
                'cycle_time' => 1,
                'quantity' => ($index + 1) * 60,
                'current_quantity' => 0,
                'started_at' => $now,
                'ended_at' => null,
                'status' => 'in_progress',
                'pause_description' => null,
            ]);

            if ($order->mould) {
                $order->mould->update([
                    'cavity_count' => 1,
                ]);
            }
        }
    }
}

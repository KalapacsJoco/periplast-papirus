<?php

namespace Tests\Unit;

use App\Models\Machine;
use App\Models\Mould;
use App\Models\Order;
use App\Models\Product;
use App\Support\MachineProductionMetrics;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class MachineProductionMetricsTest extends TestCase
{
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_it_calculates_yesterday_count_production_utilization_and_color_use(): void
    {
        CarbonImmutable::setTestNow('2026-10-19 18:00:00');

        $order = $this->order(
            currentQuantity: 150,
            createdAt: CarbonImmutable::now()->subDays(2),
            entries: [
                ['timestamp' => CarbonImmutable::now()->subHours(25)->toIso8601String(), 'current_quantity' => 100],
                ['timestamp' => CarbonImmutable::now()->subHours(2)->toIso8601String(), 'current_quantity' => 150],
            ],
        );
        $order->cycle_time = 60_000;
        $order->gross_weight = 200;
        $order->color_percentage = 5;

        $product = new Product;
        $product->planned_cycle_time = 60_000;
        $order->setRelation('product', $product);

        $mould = new Mould;
        $mould->cavity_count = 2;
        $order->setRelation('mould', $mould);

        $metrics = new MachineProductionMetrics($this->machine([$order]));

        $this->assertSame(100, $metrics->quantityAtYesterdaySameTime($order));
        $this->assertSame(50, $metrics->producedLast24Hours());
        $this->assertSame(2_880, $metrics->theoreticalDailyProduction($order));
        $this->assertEqualsWithDelta(50 / 2_880 * 100, $metrics->utilizationLast24Hours($order), 0.0001);
        $this->assertEqualsWithDelta(0.5, $metrics->dailyColorConsumptionKg($order), 0.0001);
    }

    public function test_it_identifies_the_mounted_order_and_lists_only_unactivated_waiting_orders(): void
    {
        $mounted = $this->order(10, status: 'in_progress', taskStartedAt: CarbonImmutable::now()->subHour());
        $activated = $this->order(20, status: 'in_progress', activatedAt: CarbonImmutable::now());
        $waiting = $this->order(30, status: 'waiting');
        $waiting->order_number = 'ORD-WAITING';
        $activated->order_number = 'ORD-ACTIVE';

        $metrics = new MachineProductionMetrics($this->machine([$waiting, $activated, $mounted]));

        $this->assertSame($mounted, $metrics->currentOrder());
        $this->assertSame($activated, $metrics->nextActivatedOrder());
        $this->assertSame([$waiting], $metrics->waitingOrders()->all());
    }

    public function test_it_does_not_invent_daily_production_when_historical_counts_are_missing(): void
    {
        CarbonImmutable::setTestNow('2026-10-19 18:00:00');

        $order = $this->order(
            currentQuantity: 150,
            createdAt: CarbonImmutable::now()->subDays(2),
        );
        $metrics = new MachineProductionMetrics($this->machine([$order]));

        $this->assertNull($metrics->quantityAtYesterdaySameTime($order));
        $this->assertNull($metrics->producedLast24Hours($order));
    }

    private function machine(array $orders): Machine
    {
        $machine = new Machine;
        $machine->setRelation('orders', collect($orders));

        return $machine;
    }

    private function order(
        int $currentQuantity = 0,
        string $status = 'in_progress',
        ?CarbonImmutable $createdAt = null,
        ?CarbonImmutable $taskStartedAt = null,
        ?CarbonImmutable $activatedAt = null,
        array $entries = [],
    ): Order {
        $order = new Order;
        $order->id = $currentQuantity;
        $order->order_number = 'ORD-'.$currentQuantity;
        $order->status = $status;
        $order->quantity = 1_000;
        $order->current_quantity = $currentQuantity;
        $order->created_at = $createdAt ?? CarbonImmutable::now()->subHour();
        $order->started_at = $createdAt ?? CarbonImmutable::now()->subHour();
        $order->task_started_at = $taskStartedAt;
        $order->activated_at = $activatedAt;
        $order->production_protocol = ['entries' => $entries];

        return $order;
    }
}

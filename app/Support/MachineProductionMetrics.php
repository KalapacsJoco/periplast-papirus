<?php

namespace App\Support;

use App\Models\Machine;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MachineProductionMetrics
{
    private readonly CarbonImmutable $now;

    public function __construct(private readonly Machine $machine)
    {
        $this->now = CarbonImmutable::now();
    }

    public function currentOrder(): ?Order
    {
        return $this->orders()
            ->filter(fn (Order $order): bool => in_array($order->status, ['in_progress', 'paused'], true)
                && ($order->task_started_at !== null || $order->activated_at === null))
            ->sortByDesc(fn (Order $order) => $order->task_started_at)
            ->first();
    }

    public function nextActivatedOrder(): ?Order
    {
        return $this->orders()
            ->filter(fn (Order $order): bool => $order->status === 'in_progress'
                && $order->activated_at !== null
                && $order->task_started_at === null)
            ->sortBy([
                ['date', 'asc'],
                ['id', 'asc'],
            ])
            ->first();
    }

    public function waitingOrders(): Collection
    {
        return $this->orders()
            ->filter(fn (Order $order): bool => $order->status === 'waiting'
                && $order->activated_at === null)
            ->sortBy([
                ['date', 'asc'],
                ['id', 'asc'],
            ])
            ->values();
    }

    public function quantityAtYesterdaySameTime(?Order $order): ?int
    {
        return $order === null ? null : $this->quantityAt($order, $this->now->subDay());
    }

    public function producedLast24Hours(?Order $orderFilter = null): ?int
    {
        $cutoff = $this->now->subDay();
        $produced = 0;

        foreach ($orderFilter === null ? $this->orders() : collect([$orderFilter]) as $order) {
            $hasRecentEntry = $this->hasProtocolEntryAfter($order, $cutoff);
            $baseline = $this->quantityAt($order, $cutoff);

            if ($baseline === null) {
                if ($hasRecentEntry || (int) $order->current_quantity > 0) {
                    return null;
                }

                continue;
            }

            $delta = (int) $order->current_quantity - $baseline;
            if (! $hasRecentEntry && $delta > 0) {
                return null;
            }

            $produced += max(0, $delta);
        }

        return $produced;
    }

    public function theoreticalDailyProduction(?Order $order): ?int
    {
        $cycleTime = $order?->product?->planned_cycle_time;
        $cavities = $order?->mould?->cavity_count;

        if (! $cycleTime || ! $cavities) {
            return null;
        }

        return (int) floor(86_400_000 / $cycleTime * $cavities);
    }

    public function utilizationLast24Hours(?Order $order): ?float
    {
        $theoretical = $this->theoreticalDailyProduction($order);
        $produced = $this->producedLast24Hours($order);

        if (! $theoretical || $produced === null) {
            return null;
        }

        return $produced / $theoretical * 100;
    }

    public function dailyColorConsumptionKg(?Order $order): ?float
    {
        $produced = $this->producedLast24Hours($order);

        if ($produced === null || $order?->gross_weight === null || $order->color_percentage === null) {
            return null;
        }

        return $produced * (float) $order->gross_weight * (float) $order->color_percentage / 100 / 1000;
    }

    private function quantityAt(Order $order, CarbonImmutable $time): ?int
    {
        $quantity = null;

        foreach ($this->protocolEntries($order) as $entry) {
            $timestamp = $entry['timestamp'] ?? null;
            if (! is_string($timestamp) || ! isset($entry['current_quantity'])) {
                continue;
            }

            if (CarbonImmutable::parse($timestamp)->greaterThan($time)) {
                continue;
            }

            $quantity = (int) $entry['current_quantity'];
        }

        if ($quantity !== null) {
            return $quantity;
        }

        if (
            (int) $order->current_quantity === 0
            || $order->created_at?->greaterThanOrEqualTo($time)
            || $order->started_at?->greaterThanOrEqualTo($time)
            || $order->activated_at?->greaterThanOrEqualTo($time)
        ) {
            return 0;
        }

        return null;
    }

    private function hasProtocolEntryAfter(Order $order, CarbonImmutable $time): bool
    {
        foreach ($this->protocolEntries($order) as $entry) {
            $timestamp = $entry['timestamp'] ?? null;
            if (is_string($timestamp) && CarbonImmutable::parse($timestamp)->greaterThan($time)) {
                return true;
            }
        }

        return false;
    }

    private function protocolEntries(Order $order): array
    {
        $entries = data_get($order->production_protocol, 'entries', []);

        if (! is_array($entries)) {
            return [];
        }

        $entries = array_values(array_filter(
            $entries,
            fn (mixed $entry): bool => is_array($entry)
                && is_string($entry['timestamp'] ?? null)
                && $entry['timestamp'] !== '',
        ));

        usort($entries, fn (array $left, array $right): int => CarbonImmutable::parse(
            (string) ($left['timestamp'] ?? ''),
        )->getTimestamp() <=> CarbonImmutable::parse(
            (string) ($right['timestamp'] ?? ''),
        )->getTimestamp());

        return $entries;
    }

    private function orders(): Collection
    {
        return $this->machine->orders;
    }
}

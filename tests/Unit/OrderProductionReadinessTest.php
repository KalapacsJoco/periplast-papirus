<?php

namespace Tests\Unit;

use App\Models\Machine;
use App\Models\Mould;
use App\Models\Order;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrderProductionReadinessTest extends TestCase
{
    #[DataProvider('readyEquipmentStatuses')]
    public function test_production_can_run_with_active_or_warning_equipment(string $machineStatus, string $mouldStatus): void
    {
        $order = $this->order($machineStatus, $mouldStatus);

        $this->assertTrue($order->canRunProduction());
    }

    public function test_production_cannot_run_when_machine_is_not_operational(): void
    {
        $order = $this->order('stopped', 'active');

        $this->assertFalse($order->canRunProduction());
    }

    public function test_production_cannot_run_when_mould_is_not_operational(): void
    {
        $order = $this->order('active', 'needs_repair');

        $this->assertFalse($order->canRunProduction());
    }

    public function test_production_cannot_run_with_an_open_quality_issue(): void
    {
        $order = $this->order('active', 'active', [
            'quality_status' => 'good',
            'quality_issues' => [
                ['id' => 'issue-1', 'status' => 'open'],
            ],
        ]);

        $this->assertFalse($order->canRunProduction());
    }

    public function test_order_can_be_activated_with_a_stopped_machine_and_non_ready_mould_or_quality(): void
    {
        $order = $this->order('stopped', 'standby', [
            'quality_status' => 'quality_issue',
            'quality_issues' => [
                ['id' => 'issue-1', 'status' => 'open'],
            ],
        ]);

        $this->assertTrue($order->canBeActivated());
    }

    public function test_order_cannot_be_activated_when_machine_is_broken(): void
    {
        $order = $this->order('broken', 'active');

        $this->assertFalse($order->canBeActivated());
    }

    public static function readyEquipmentStatuses(): array
    {
        return [
            'both active' => ['active', 'active'],
            'machine warning' => ['warning', 'active'],
            'mould warning' => ['active', 'warning'],
            'both warning' => ['warning', 'warning'],
        ];
    }

    private function order(string $machineStatus, string $mouldStatus, array $protocol = []): Order
    {
        $order = new Order;
        $order->production_protocol = $protocol;

        $machine = new Machine;
        $machine->status = $machineStatus;

        $mould = new Mould;
        $mould->status = $mouldStatus;

        $order->setRelation('machine', $machine);
        $order->setRelation('mould', $mould);

        return $order;
    }
}

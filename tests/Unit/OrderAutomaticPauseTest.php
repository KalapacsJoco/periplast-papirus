<?php

namespace Tests\Unit;

use App\Models\Machine;
use App\Models\Mould;
use App\Models\Order;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderAutomaticPauseTest extends TestCase
{
    private string $originalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConnection = config('database.default');
        $connection = config('database.connections.sqlite');
        $connection['database'] = ':memory:';
        config([
            'database.connections.production-pause-test' => $connection,
            'database.default' => 'production-pause-test',
        ]);
        DB::purge('production-pause-test');

        Schema::create('machines', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type')->default('press');
            $table->string('status')->default('active');
            $table->text('problem_description')->nullable();
            $table->timestamps();
        });

        Schema::create('moulds', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('status')->default('active');
            $table->text('problem_description')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('machine_id');
            $table->foreignId('mould_id');
            $table->string('status');
            $table->unsignedInteger('quantity')->default(100);
            $table->unsignedInteger('current_quantity')->default(0);
            $table->unsignedInteger('good_quantity')->default(0);
            $table->text('pause_description')->nullable();
            $table->timestamp('last_paused_at')->nullable();
            $table->json('production_protocol')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::purge('production-pause-test');
        config(['database.default' => $this->originalConnection]);

        parent::tearDown();
    }

    public function test_machine_fault_pauses_order_and_recovery_resumes_it(): void
    {
        [$machine, $mould, $order] = $this->runningOrder();

        $machine->update(['status' => 'stopped', 'problem_description' => 'Hydraulic pressure loss']);

        $this->assertSame('paused', $order->fresh()->status);
        $this->assertSame('Hydraulic pressure loss', $order->fresh()->pause_description);

        $machine->update(['status' => 'active', 'problem_description' => null]);

        $this->assertSame('in_progress', $order->fresh()->status);
        $this->assertNull($order->fresh()->pause_description);
    }

    public function test_order_resumes_only_after_every_equipment_fault_is_resolved(): void
    {
        [$machine, $mould, $order] = $this->runningOrder();

        $machine->update(['status' => 'stopped', 'problem_description' => 'Machine fault']);
        $mould->update(['status' => 'needs_repair', 'problem_description' => 'Mould fault']);

        $machine->update(['status' => 'active', 'problem_description' => null]);
        $this->assertSame('paused', $order->fresh()->status);

        $mould->update(['status' => 'active', 'problem_description' => null]);
        $this->assertSame('in_progress', $order->fresh()->status);
    }

    public function test_resolving_quality_issue_resumes_an_order_when_equipment_is_ready(): void
    {
        [, , $order] = $this->runningOrder();
        $issue = ['id' => 'quality-1', 'status' => 'open'];
        $order->update([
            'production_protocol' => [
                'quality_status' => 'quality_issue',
                'quality_issues' => [$issue],
            ],
        ]);
        $order->pauseForIssue('quality:quality-1', 'Surface defect');

        $issue['status'] = 'resolved';
        $protocol = $order->fresh()->production_protocol;
        $protocol['quality_status'] = 'good';
        $protocol['quality_issues'] = [$issue];
        $order->update([
            'production_protocol' => $protocol,
        ]);
        $order->resolveIssuePause('quality:quality-1');

        $this->assertSame('in_progress', $order->fresh()->status);
    }

    public function test_manual_pause_is_not_removed_when_equipment_fault_is_resolved(): void
    {
        [$machine, , $order] = $this->runningOrder('paused');
        $order->update(['pause_description' => 'Manual pause']);

        $machine->update(['status' => 'stopped', 'problem_description' => 'Machine fault']);
        $machine->update(['status' => 'active', 'problem_description' => null]);

        $this->assertSame('paused', $order->fresh()->status);
        $this->assertSame('Manual pause', $order->fresh()->pause_description);
    }

    private function runningOrder(string $status = 'in_progress'): array
    {
        $machine = Machine::query()->create([
            'name' => 'Machine 1',
            'type' => 'press',
            'status' => 'active',
        ]);
        $mould = Mould::query()->create([
            'name' => 'Mould 1',
            'status' => 'active',
        ]);
        $order = Order::query()->create([
            'machine_id' => $machine->id,
            'mould_id' => $mould->id,
            'status' => $status,
            'quantity' => 100,
            'current_quantity' => 20,
            'good_quantity' => 20,
            'production_protocol' => [],
        ]);

        return [$machine, $mould, $order];
    }
}

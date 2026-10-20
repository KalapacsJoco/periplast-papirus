<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('measured_cycle_time')->nullable()->comment('Measured cycle time in milliseconds');
            $table->decimal('measured_net_weight', 10, 3)->nullable();
            $table->decimal('measured_gross_weight', 10, 3)->nullable();
            $table->decimal('measured_color_percentage', 8, 2)->nullable();
            $table->timestamp('measured_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'measured_cycle_time',
                'measured_net_weight',
                'measured_gross_weight',
                'measured_color_percentage',
                'measured_at',
            ]);
        });
    }
};

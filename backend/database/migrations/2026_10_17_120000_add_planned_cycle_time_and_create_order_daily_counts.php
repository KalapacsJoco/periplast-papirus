<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('planned_cycle_time')->nullable()->comment('Theoretical cycle time in milliseconds');
        });

        Schema::create('order_daily_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('count')->comment('Counter reading taken in the morning');
            $table->timestamps();
            $table->unique(['order_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_daily_counts');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('planned_cycle_time');
        });
    }
};

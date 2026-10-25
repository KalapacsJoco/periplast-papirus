<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('moulds', function (Blueprint $table) {
            // Basic technical data
            $table->unsignedInteger('height_mm')->nullable();
            $table->unsignedInteger('cooling_water_channel')->nullable();

            // Hot runner
            $table->boolean('hot_channel')->default(false);
            $table->unsignedInteger('hot_channel_count')->nullable();

            // Hot runner controller types
            $table->boolean('hot_channel_5a')->default(false);
            $table->boolean('hot_channel_220v')->default(false);

            // Temperature control
            $table->boolean('temper')->default(false);
            $table->unsignedInteger('temper_water_circuit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moulds', function (Blueprint $table) {
            $table->dropColumn([
                'height_mm',
                'cooling_water_channel',
                'hot_channel',
                'hot_channel_count',
                'hot_channel_5a',
                'hot_channel_220v',
                'temper',
                'temper_water_circuit',
            ]);
        });
    }
};

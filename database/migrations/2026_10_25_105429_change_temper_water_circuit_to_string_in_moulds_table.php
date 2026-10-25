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
             $table->string('temper_water_circuit')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moulds', function (Blueprint $table) {
            $table->integer('temper_water_circuit')->nullable()->change();
        });
    }
};

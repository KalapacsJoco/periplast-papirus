<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('additive_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('additive_id')->constrained('additives')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'additive_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('additive_product');
    }
};

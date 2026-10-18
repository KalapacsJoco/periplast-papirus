<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inserts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mould_id')->constrained('moulds')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name');
            $table->string('number');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['mould_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inserts');
    }
};

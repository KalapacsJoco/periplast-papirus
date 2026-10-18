<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mould_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mould_id')->constrained('moulds')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['mould_id', 'product_id']);
        });

        Schema::table('moulds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('moulds', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('id')->constrained('products')->nullOnDelete();
        });

        Schema::dropIfExists('mould_product');
    }
};

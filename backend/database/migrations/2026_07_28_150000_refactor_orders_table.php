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
        Schema::table('orders', function (Blueprint $table) {
            // Add new columns
            $table->string('order_number')->unique()->after('id');
            $table->foreignId('mould_id')->nullable()->constrained('moulds')->onDelete('set null')->after('machine_id');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade')->after('mould_id');
            $table->unsignedInteger('cycle_time')->nullable()->comment('Cycle time in milliseconds')->after('product_id');
            $table->decimal('net_weight', 10, 3)->nullable()->comment('Product weight only')->after('cycle_time');
            $table->decimal('gross_weight', 10, 3)->nullable()->comment('Product weight + waste')->after('net_weight');
            $table->decimal('color_percentage', 5, 2)->nullable()->comment('Color percentage relative to product weight')->after('gross_weight');
            $table->json('additives_percentages')->nullable()->comment('JSON array of {additive_id, percentage}')->after('color_percentage');
            $table->text('pause_description')->nullable()->comment('Reason for pause status')->after('additives_percentages');
            
            // Modify status enum values
            $table->string('status')->default('waiting')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_number']);
            $table->dropColumn([
                'order_number',
                'mould_id',
                'product_id',
                'cycle_time',
                'net_weight',
                'gross_weight',
                'color_percentage',
                'additives_percentages',
                'pause_description',
            ]);
            $table->string('status')->default('pending')->change();
        });
    }
};

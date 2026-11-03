<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('color_percentage', 8, 2)->nullable()->after('color_id');
        });

        Schema::table('additive_product', function (Blueprint $table) {
            $table->decimal('percentage', 8, 2)->nullable()->after('additive_id');
        });
    }

    public function down(): void
    {
        Schema::table('additive_product', function (Blueprint $table) {
            $table->dropColumn('percentage');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('color_percentage');
        });
    }
};

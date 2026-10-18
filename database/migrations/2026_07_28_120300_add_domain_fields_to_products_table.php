<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->string('code')->nullable(false)->change();
            $table->foreignId('material_id')->nullable()->after('code')->constrained('materials')->nullOnDelete();
            $table->foreignId('color_id')->nullable()->after('material_id')->constrained('colors')->nullOnDelete();
            $table->string('customer_code')->nullable()->after('name');
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropConstrainedForeignId('material_id');
            $table->dropConstrainedForeignId('color_id');
            $table->dropColumn('customer_code');
            $table->string('code')->nullable()->change();
            $table->unique('code');
        });
    }
};

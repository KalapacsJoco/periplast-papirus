<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moulds', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('id')->constrained('products')->nullOnDelete();
            $table->string('number')->nullable()->after('name');
            $table->unsignedInteger('cavity_count')->nullable()->after('number');
        });
    }

    public function down(): void
    {
        Schema::table('moulds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn(['number', 'cavity_count']);
        });
    }
};

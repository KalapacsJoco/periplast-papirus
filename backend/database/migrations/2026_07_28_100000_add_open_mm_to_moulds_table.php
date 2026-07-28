<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moulds', function (Blueprint $table) {
            $table->decimal('open_mm', 8, 2)->nullable()->after('cavity_count')->comment('Nyitás mm-ben');
        });
    }

    public function down(): void
    {
        Schema::table('moulds', function (Blueprint $table) {
            $table->dropColumn('open_mm');
        });
    }
};

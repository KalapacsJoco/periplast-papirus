<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mould_repair_requests', function (Blueprint $table) {
            $table->boolean('cavities_reduced')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('mould_repair_requests', fn (Blueprint $table) => $table->dropColumn('cavities_reduced'));
    }
};

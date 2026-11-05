<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moulds', function (Blueprint $table) {
            $table->unsignedInteger('active_cavityes')->nullable()->after('cavity_count')->comment('Aktív (nem lezárt) fészkek száma');
        });

        DB::table('moulds')->update(['active_cavityes' => DB::raw('cavity_count')]);
    }

    public function down(): void
    {
        Schema::table('moulds', function (Blueprint $table) {
            $table->dropColumn('active_cavityes');
        });
    }
};

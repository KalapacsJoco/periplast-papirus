<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moulds', function (Blueprint $table) {
            $table->text('problem_description')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('moulds', function (Blueprint $table) {
            $table->dropColumn('problem_description');
        });
    }
};

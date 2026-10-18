<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('errors', function (Blueprint $table) {
            $table->dropForeign(['machine_id']);
            $table->unsignedBigInteger('machine_id')->nullable()->change();
            $table->foreign('machine_id')->references('id')->on('machines')->nullOnDelete();
            $table->foreignId('mould_id')->nullable()->after('machine_id')->constrained('moulds')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('errors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mould_id');
            $table->dropForeign(['machine_id']);
            $table->unsignedBigInteger('machine_id')->nullable(false)->change();
            $table->foreign('machine_id')->references('id')->on('machines')->cascadeOnDelete();
        });
    }
};

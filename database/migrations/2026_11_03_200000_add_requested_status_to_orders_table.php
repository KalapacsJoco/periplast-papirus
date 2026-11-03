<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'requested_status')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('requested_status')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'requested_status')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('requested_status');
            });
        }
    }
};

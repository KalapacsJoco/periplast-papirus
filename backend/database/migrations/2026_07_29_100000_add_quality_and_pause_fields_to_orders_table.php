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
            if (! Schema::hasColumn('orders', 'good_quantity')) {
                $table->unsignedInteger('good_quantity')->default(0)->after('current_quantity');
            }

            if (! Schema::hasColumn('orders', 'bad_quantity')) {
                $table->unsignedInteger('bad_quantity')->default(0)->after('good_quantity');
            }

            if (! Schema::hasColumn('orders', 'last_paused_at')) {
                $table->timestamp('last_paused_at')->nullable()->after('ended_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'last_paused_at')) {
                $table->dropColumn('last_paused_at');
            }

            if (Schema::hasColumn('orders', 'bad_quantity')) {
                $table->dropColumn('bad_quantity');
            }

            if (Schema::hasColumn('orders', 'good_quantity')) {
                $table->dropColumn('good_quantity');
            }
        });
    }
};

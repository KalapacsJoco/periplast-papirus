<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('activated_at')->nullable()->after('task_started_at');
        });

        DB::table('notifications')->orderBy('created_at')->each(function (object $notification): void {
            $data = json_decode($notification->data, true);

            if (($data['event'] ?? null) !== 'activated' || empty($data['order_id'])) {
                return;
            }

            DB::table('orders')
                ->where('id', $data['order_id'])
                ->whereNull('activated_at')
                ->update(['activated_at' => $notification->created_at]);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('activated_at');
        });
    }
};

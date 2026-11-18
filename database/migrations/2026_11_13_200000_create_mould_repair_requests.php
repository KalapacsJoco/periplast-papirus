<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moulds', function (Blueprint $table) {
            $table->unsignedInteger('repair_issue_version')->default(0);
        });

        Schema::create('mould_repair_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mould_id')->constrained()->restrictOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description');
            $table->unsignedTinyInteger('priority');
            $table->string('assigned_role');
            $table->string('status')->default('waiting');
            $table->unsignedInteger('issue_version')->default(0);
            $table->timestamps();
            $table->index(['mould_id', 'status']);
        });

        Schema::create('mould_repair_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mould_repair_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repaired_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assigned_role');
            $table->text('description');
            $table->unsignedInteger('duration_minutes');
            $table->timestamp('repaired_at');
            $table->boolean('test_passed')->nullable();
            $table->text('test_note')->nullable();
            $table->foreignId('tested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tested_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mould_repair_attempts');
        Schema::dropIfExists('mould_repair_requests');
        Schema::table('moulds', fn (Blueprint $table) => $table->dropColumn('repair_issue_version'));
    }
};

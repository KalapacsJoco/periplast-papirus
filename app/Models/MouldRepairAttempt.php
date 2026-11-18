<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouldRepairAttempt extends Model
{
    protected $fillable = [
        'mould_repair_request_id', 'repaired_by', 'assigned_role', 'description', 'duration_minutes',
        'repaired_at', 'test_passed', 'test_note', 'tested_by', 'tested_at',
    ];

    protected function casts(): array
    {
        return ['test_passed' => 'boolean', 'repaired_at' => 'datetime', 'tested_at' => 'datetime'];
    }

    public function repairer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'repaired_by');
    }

    public function tester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tested_by');
    }
}

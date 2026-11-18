<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MouldRepairRequest extends Model
{
    protected $fillable = ['mould_id', 'requested_by', 'description', 'priority', 'assigned_role', 'status', 'issue_version'];

    public function mould(): BelongsTo
    {
        return $this->belongsTo(Mould::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(MouldRepairAttempt::class);
    }
}

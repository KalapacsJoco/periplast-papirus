<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    protected $fillable = ['machine_id', 'mould_id', 'date', 'type', 'description', 'status'];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function mould(): BelongsTo
    {
        return $this->belongsTo(Mould::class);
    }
}

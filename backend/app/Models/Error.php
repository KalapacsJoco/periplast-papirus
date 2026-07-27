<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Error extends Model
{
    protected $fillable = ['machine_id', 'date', 'type', 'severity', 'description', 'solution', 'status'];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}

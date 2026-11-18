<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouldNote extends Model
{
    protected $fillable = ['mould_id', 'user_id', 'body'];

    public function mould(): BelongsTo
    {
        return $this->belongsTo(Mould::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

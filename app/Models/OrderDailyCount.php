<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDailyCount extends Model
{
    protected $fillable = ['order_id', 'date', 'count'];

    protected $casts = [
        'date' => 'date',
        'count' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

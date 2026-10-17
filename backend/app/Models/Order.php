<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'machine_id',
        'mould_id',
        'product_id',
        'date',
        'customer',
        'quantity',
        'current_quantity',
        'good_quantity',
        'bad_quantity',
        'started_at',
        'ended_at',
        'last_paused_at',
        'cycle_time',
        'net_weight',
        'gross_weight',
        'color_percentage',
        'additives_percentages',
        'production_protocol',
        'status',
        'pause_description',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'integer',
        'current_quantity' => 'integer',
        'good_quantity' => 'integer',
        'bad_quantity' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_paused_at' => 'datetime',
        'cycle_time' => 'integer',
        'net_weight' => 'decimal:3',
        'gross_weight' => 'decimal:3',
        'color_percentage' => 'decimal:2',
        'additives_percentages' => 'array',
        'production_protocol' => 'array',
    ];

    public function dailyCounts(): HasMany
    {
        return $this->hasMany(OrderDailyCount::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function mould(): BelongsTo
    {
        return $this->belongsTo(Mould::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

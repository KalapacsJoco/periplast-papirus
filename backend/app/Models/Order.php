<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'cycle_time',
        'net_weight',
        'gross_weight',
        'color_percentage',
        'additives_percentages',
        'status',
        'pause_description',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'integer',
        'cycle_time' => 'integer',
        'net_weight' => 'decimal:3',
        'gross_weight' => 'decimal:3',
        'color_percentage' => 'decimal:2',
        'additives_percentages' => 'array',
    ];

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAdditive extends Model
{
    protected $table = 'additive_product';

    protected $fillable = [
        'additive_id',
        'percentage',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function additive(): BelongsTo
    {
        return $this->belongsTo(Additive::class);
    }
}

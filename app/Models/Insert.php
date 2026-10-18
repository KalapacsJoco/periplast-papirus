<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insert extends Model
{
    use HasFactory;

    protected $fillable = [
        'mould_id',
        'product_id',
        'name',
        'number',
        'description',
        'status',
    ];

    public function mould(): BelongsTo
    {
        return $this->belongsTo(Mould::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

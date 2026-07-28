<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'customer_code',
        'material_id',
        'color_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function moulds(): BelongsToMany
    {
        return $this->belongsToMany(Mould::class);
    }

    public function additives(): BelongsToMany
    {
        return $this->belongsToMany(Additive::class);
    }

    public function inserts(): HasMany
    {
        return $this->hasMany(Insert::class);
    }
}

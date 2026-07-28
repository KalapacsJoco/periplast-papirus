<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mould extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'number',
        'cavity_count',
        'open_mm',
        'description',
        'status',
        'problem_description',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function machines(): BelongsToMany
    {
        return $this->belongsToMany(Machine::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function errors(): HasMany
    {
        return $this->hasMany(Error::class);
    }

    public function inserts(): HasMany
    {
        return $this->hasMany(Insert::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}

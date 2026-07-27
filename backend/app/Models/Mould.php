<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mould extends Model
{
    protected $fillable = ['name', 'type', 'description', 'status'];

    public function machines(): BelongsToMany
    {
        return $this->belongsToMany(Machine::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }
}

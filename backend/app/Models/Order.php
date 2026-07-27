<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    protected $fillable = ['machine_id', 'date', 'customer', 'quantity', 'status', 'description'];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function moulds(): BelongsToMany
    {
        return $this->belongsToMany(Mould::class);
    }
}

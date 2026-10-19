<?php

namespace App\Models;

use App\Support\MachineProductionMetrics;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'open_mm', 'screw_diameter_cm', 'description', 'status', 'problem_description'];

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function errors(): HasMany
    {
        return $this->hasMany(Error::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function productionMetrics(): MachineProductionMetrics
    {
        return new MachineProductionMetrics($this);
    }

    public function moulds(): BelongsToMany
    {
        return $this->belongsToMany(Mould::class);
    }
}

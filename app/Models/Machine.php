<?php

namespace App\Models;

use App\Support\MachineProductionMetrics;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'screw_diameter_cm', 'description', 'status', 'problem_description', 'opening', 'closing', 'pressure'];

    protected static function booted(): void
    {
        static::updated(function (Machine $machine): void {
            if (! $machine->wasChanged('status')) {
                return;
            }

            if (in_array($machine->status, ['stopped', 'broken', 'inactive', 'maintenance'], true)) {
                Order::pauseForEquipmentIssue(
                    'machine',
                    $machine->id,
                    $machine->problem_description ?: $machine->status,
                );

                return;
            }

            if (in_array($machine->status, ['active', 'warning'], true)) {
                Order::resolveEquipmentIssue('machine', $machine->id);
            }
        });
    }

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

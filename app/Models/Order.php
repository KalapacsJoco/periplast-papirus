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
        'assigned_operator_id',
        'task_started_at',
        'activated_at',
        'date',
        'planned_end_date',
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
        'measured_cycle_time',
        'measured_net_weight',
        'measured_gross_weight',
        'measured_color_percentage',
        'measured_at',
        'additives_percentages',
        'production_protocol',
        'status',
        'pause_description',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'planned_end_date' => 'date',
        'quantity' => 'integer',
        'current_quantity' => 'integer',
        'good_quantity' => 'integer',
        'bad_quantity' => 'integer',
        'started_at' => 'datetime',
        'task_started_at' => 'datetime',
        'activated_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_paused_at' => 'datetime',
        'cycle_time' => 'integer',
        'net_weight' => 'decimal:3',
        'gross_weight' => 'decimal:3',
        'color_percentage' => 'decimal:2',
        'measured_cycle_time' => 'integer',
        'measured_net_weight' => 'decimal:3',
        'measured_gross_weight' => 'decimal:3',
        'measured_color_percentage' => 'decimal:2',
        'measured_at' => 'datetime',
        'additives_percentages' => 'array',
        'production_protocol' => 'array',
    ];

    public const MEASURED_TOLERANCE_PERCENT = 0.2;

    public const MEASURED_FIELDS = ['cycle_time', 'net_weight', 'gross_weight', 'color_percentage'];

    public function hasMeasuredData(): bool
    {
        foreach (self::MEASURED_FIELDS as $field) {
            if ($this->{'measured_'.$field} === null) {
                return false;
            }
        }

        return true;
    }

    /** True when the measured value differs from the theoretical one by more than the tolerance (relative, in %). */
    public function measuredDeviates(string $field): bool
    {
        $measured = $this->{'measured_'.$field};
        $theoretical = $this->{$field};

        if ($measured === null || $theoretical === null) {
            return false;
        }

        $measured = (float) $measured;
        $theoretical = (float) $theoretical;

        if ($theoretical == 0.0) {
            return $measured != 0.0;
        }

        return abs($measured - $theoretical) / abs($theoretical) * 100 > self::MEASURED_TOLERANCE_PERCENT;
    }

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

    public function assignedOperator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_operator_id');
    }
}

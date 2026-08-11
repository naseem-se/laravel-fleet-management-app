<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelEntry extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'vehicle_id', 'journey_id', 'driver_id',
        'quantity_litres', 'rate_per_litre', 'total_cost',
        'odometer_reading', 'receipt_photo_path', 'entry_time',
    ];

    protected $casts = [
        'quantity_litres' => 'decimal:2',
        'rate_per_litre' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'odometer_reading' => 'decimal:2',
        'entry_time' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
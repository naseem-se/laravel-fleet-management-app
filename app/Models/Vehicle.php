<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'assigned_driver_id', 'registration_number', 'qr_code_value',
        'make', 'model', 'year', 'vehicle_type', 'engine_number', 'chassis_number',
        'fuel_type', 'tank_capacity_litres', 'current_odometer', 'status',
    ];

    protected $casts = [
        'tank_capacity_litres' => 'decimal:2',
        'current_odometer' => 'decimal:2',
        'avg_kmpl_cached' => 'decimal:2',
        'last_lat' => 'decimal:7',
        'last_lng' => 'decimal:7',
        'last_location_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Vehicle $vehicle) {
            // Random token, never derived from the plate number — see design notes
            // in the project brief on why: prevents QR spoofing from a guessed plate.
            if (empty($vehicle->qr_code_value)) {
                $vehicle->qr_code_value = (string) Str::uuid();
            }
        });
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'assigned_driver_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }

    public function journeys(): HasMany
    {
        return $this->hasMany(Journey::class);
    }

    public function fuelEntries(): HasMany
    {
        return $this->hasMany(FuelEntry::class);
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    public function activeJourney(): HasMany
    {
        return $this->hasMany(Journey::class)->where('status', 'active');
    }
}
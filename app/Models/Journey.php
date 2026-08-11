<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journey extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'vehicle_id', 'driver_id', 'status',
        'start_km', 'start_photo_path', 'start_lat', 'start_lng', 'start_time',
        'end_km', 'end_photo_path', 'end_lat', 'end_lng', 'end_time',
        'total_distance', 'duration_minutes',
    ];

    protected $casts = [
        'start_km' => 'decimal:2',
        'end_km' => 'decimal:2',
        'start_lat' => 'decimal:7',
        'start_lng' => 'decimal:7',
        'end_lat' => 'decimal:7',
        'end_lng' => 'decimal:7',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_distance' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(JourneyLocation::class);
    }

    public function fuelEntries(): HasMany
    {
        return $this->hasMany(FuelEntry::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'user_id', 'name', 'phone', 'cnic_number',
        'license_number', 'license_expiry_date', 'status', 'pin_hash',
        'profile_photo_path',
    ];

    protected $hidden = [
        'pin_hash',
    ];

    protected $casts = [
        'license_expiry_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedVehicle(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'assigned_driver_id');
    }

    public function journeys(): HasMany
    {
        return $this->hasMany(Journey::class);
    }

    public function fuelEntries(): HasMany
    {
        return $this->hasMany(FuelEntry::class);
    }
    public function documents(): HasMany
    {
        return $this->hasMany(DriverDocument::class);
    }

    public function isLicenseExpiringSoon(int $withinDays = 30): bool
    {
        return $this->license_expiry_date
            && $this->license_expiry_date->isBefore(now()->addDays($withinDays));
    }
}
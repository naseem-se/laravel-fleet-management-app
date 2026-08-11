<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'vehicle_id', 'type', 'description', 'cost',
        'odometer_at_service', 'service_date', 'next_service_date',
        'next_service_km', 'performed_by',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'odometer_at_service' => 'decimal:2',
        'next_service_km' => 'decimal:2',
        'service_date' => 'date',
        'next_service_date' => 'date',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleDocument extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'vehicle_id', 'document_type', 'document_number',
        'issue_date', 'expiry_date', 'file_path', 'reminder_sent_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'reminder_sent_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function isExpiringSoon(int $withinDays = 30): bool
    {
        return $this->expiry_date
            && $this->expiry_date->isBefore(now()->addDays($withinDays));
    }
}
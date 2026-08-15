<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverDocument extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'driver_id', 'document_type', 'document_number',
        'issue_date', 'expiry_date', 'file_path',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function isExpiringSoon(int $withinDays = 30): bool
    {
        return $this->expiry_date && $this->expiry_date->isBefore(now()->addDays($withinDays));
    }
}
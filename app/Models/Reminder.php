<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Reminder extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'reminder_type', 'reference_type', 'reference_id',
        'due_date', 'due_km', 'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'due_km' => 'decimal:2',
    ];

    // Points at a MaintenanceRecord, VehicleDocument, or Driver
    // depending on reminder_type — see the morphs('reference') migration.
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
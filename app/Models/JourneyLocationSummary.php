<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JourneyLocationSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'journey_id', 'point_count', 'min_lat', 'max_lat', 'min_lng', 'max_lng',
        'max_speed_kmh', 'avg_speed_kmh', 'first_recorded_at', 'last_recorded_at', 'archived_at',
    ];

    protected $casts = [
        'min_lat' => 'decimal:7', 'max_lat' => 'decimal:7',
        'min_lng' => 'decimal:7', 'max_lng' => 'decimal:7',
        'max_speed_kmh' => 'decimal:2', 'avg_speed_kmh' => 'decimal:2',
        'first_recorded_at' => 'datetime', 'last_recorded_at' => 'datetime', 'archived_at' => 'datetime',
    ];

    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }
}
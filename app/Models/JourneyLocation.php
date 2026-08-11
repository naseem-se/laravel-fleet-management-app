<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JourneyLocation extends Model
{
    use HasFactory;

    // Intentionally NOT company-scoped: this table has no company_id column
    // (see migration notes — kept lean since it's high volume). Always query
    // it through the journeys relationship, which is itself company-scoped.
    public $timestamps = false;

    protected $fillable = [
        'journey_id', 'lat', 'lng', 'speed_kmh', 'recorded_at',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'speed_kmh' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }
}
<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class JourneyLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $journeyId,
        public int $companyId,
        public float $lat,
        public float $lng,
        public ?float $speedKmh,
        public string $recordedAt,
        public ?float $accuracyMeters = null,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel("company.{$this->companyId}.journeys")];
    }

    public function broadcastAs(): string
    {
        return 'journey.location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'journey_id' => $this->journeyId,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'speed_kmh' => $this->speedKmh,
            'recorded_at' => $this->recordedAt,
            'accuracy_meters' => $this->accuracyMeters,
        ];
    }
}
<?php

namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class JourneyStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public int $journeyId,
        public int $companyId,
        public string $status,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel("company.{$this->companyId}.journeys")];
    }

    public function broadcastAs(): string
    {
        return 'journey.status.changed';
    }

    public function broadcastWith(): array
    {
        return ['journey_id' => $this->journeyId, 'status' => $this->status];
    }
}
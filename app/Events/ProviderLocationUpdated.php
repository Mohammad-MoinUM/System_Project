<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ProviderLocationUpdated implements ShouldBroadcast
{
    public function __construct(
        public int $bookingId,
        public int $providerId,
        public float $latitude,
        public float $longitude,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("booking.{$this->bookingId}");
    }

    public function broadcastAs(): string
    {
        return 'provider.location.updated';
    }
}
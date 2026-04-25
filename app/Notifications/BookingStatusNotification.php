<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking, public string $newStatus) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $serviceName = $this->booking->service?->name ?? 'Your booking';
        $statusLabel = ucfirst(str_replace('_', ' ', $this->newStatus));

        return [
            'title' => "Booking {$statusLabel}",
            'message' => "{$serviceName} has been marked as {$statusLabel}.",
            'icon' => match ($this->newStatus) {
                'completed' => 'check-circle',
                'cancelled' => 'x-circle',
                'active',
                'in_progress' => 'arrow-path',
                default => 'information-circle',
            },
            'booking_id' => $this->booking->id,
            'type' => 'booking_status',
        ];
    }
}

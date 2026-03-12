<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBookingNotification extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $serviceName = $this->booking->service?->name ?? 'a service';
        $customerName = $this->booking->taker?->name ?? 'A customer';

        return [
            'title' => 'New Booking Request',
            'message' => "{$customerName} booked {$serviceName}.",
            'icon' => 'clipboard-document-check',
            'booking_id' => $this->booking->id,
            'type' => 'new_booking',
        ];
    }
}

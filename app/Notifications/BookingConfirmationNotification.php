<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmationNotification extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking Confirmation #' . $this->booking->id)
            ->line('Your booking has been created successfully.')
            ->line('Service: ' . ($this->booking->service?->name ?? 'Service'))
            ->line('Scheduled at: ' . optional($this->booking->scheduled_at)->format('M d, Y g:i A'))
            ->line('Total: BDT ' . number_format((float) $this->booking->total, 2))
            ->action('View Booking', route('booking.show', $this->booking));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Booking Confirmed',
            'message' => 'Your booking #' . $this->booking->id . ' has been created.',
            'icon' => 'check-badge',
            'booking_id' => $this->booking->id,
            'type' => 'booking_confirmed',
        ];
    }
}

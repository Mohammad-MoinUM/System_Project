<?php

namespace App\Observers;

use App\Models\Booking;
use App\Notifications\BookingStatusNotification;
use App\Notifications\NewBookingNotification;
use Illuminate\Support\Facades\Auth;

class BookingObserver
{
    public function created(Booking $booking): void
    {
        // Load relationships
        $booking->loadMissing(['service', 'taker', 'provider']);

        // Notify the provider about a new booking
        if ($booking->provider) {
            $booking->provider->notify(new NewBookingNotification($booking));
        }
    }

    public function updated(Booking $booking): void
    {
        // Notify when booking status changes
        if ($booking->isDirty('status')) {

            $booking->loadMissing(['service', 'taker', 'provider']);

            $newStatus = $booking->status;

            // Notify the customer
            if ($booking->taker) {
                $booking->taker->notify(
                    new BookingStatusNotification($booking, $newStatus)
                );
            }

            // Notify the provider (if change not made by provider)
            if ($booking->provider && $booking->provider->id !== Auth::id()) {
                $booking->provider->notify(
                    new BookingStatusNotification($booking, $newStatus)
                );
            }
        }
    }
}
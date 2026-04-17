<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    public function sendBookingConfirmation(string $phone, string $message): void
    {
        // Placeholder transport. Replace with gateway integration for production.
        Log::info('sms.booking_confirmation', [
            'phone' => $phone,
            'message' => $message,
        ]);
    }
}

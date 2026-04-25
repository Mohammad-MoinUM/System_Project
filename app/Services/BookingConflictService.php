<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;

class BookingConflictService
{
    /**
     * Check if a booking would conflict with existing bookings
     * 
     * @param int $providerId
     * @param string $date (format: YYYY-MM-DD)
     * @param string $timeFrom (format: HH:mm)
     * @param string $timeTo (format: HH:mm)
     * @param int|null $excludeBookingId - Booking ID to exclude from check (useful for updates)
     * @return array ['conflicts' => bool, 'conflicting_booking' => Booking|null, 'message' => string]
     */
    public function checkConflict(int $providerId, string $date, string $timeFrom, string $timeTo, ?int $excludeBookingId = null): array
    {
        $query = Booking::where('provider_id', $providerId)
            ->where('booking_date', $date)
            ->whereNotIn('status', ['cancelled', 'rejected']);
        
        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }
        
        $existingBookings = $query->get(['id', 'time_from', 'time_to', 'status']);
        
        foreach ($existingBookings as $booking) {
            if ($this->timesOverlap($timeFrom, $timeTo, $booking->time_from, $booking->time_to)) {
                return [
                    'conflicts' => true,
                    'conflicting_booking' => $booking,
                    'message' => "This time slot conflicts with existing {$booking->status} booking.",
                ];
            }
        }
        
        return [
            'conflicts' => false,
            'conflicting_booking' => null,
            'message' => 'No conflicts detected.',
        ];
    }

    /**
     * Check if a provider has availability for given time
     * 
     * @param int $providerId
     * @param string $date (format: YYYY-MM-DD)
     * @param string $timeFrom (format: HH:mm)
     * @param string $timeTo (format: HH:mm)
     * @return array ['available' => bool, 'message' => string]
     */
    public function isProviderAvailable(int $providerId, string $date, string $timeFrom, string $timeTo): array
    {
        $dateObj = Carbon::parse($date);
        $dayName = $dateObj->format('l');
        
        $availability = \App\Models\ProviderAvailability::where('user_id', $providerId)
            ->where('day_of_week', $dayName)
            ->where('is_available', true)
            ->first();
        
        if (!$availability) {
            return [
                'available' => false,
                'message' => "Provider is not available on {$dayName}.",
            ];
        }
        
        // Check if requested time is within availability window
        $timeFromObj = $this->parseTime($timeFrom);
        $timeToObj = $this->parseTime($timeTo);
        $availStart = $this->parseTime((string) $availability->start_time);
        $availEnd = $this->parseTime((string) $availability->end_time);

        if (!$timeFromObj || !$timeToObj || !$availStart || !$availEnd) {
            return [
                'available' => false,
                'message' => 'Unable to validate provider availability for the selected time.',
            ];
        }
        
        if ($timeFromObj->lt($availStart) || $timeToObj->gt($availEnd)) {
            return [
                'available' => false,
                'message' => "Requested time is outside provider's available hours ({$availStart->format('H:i')} - {$availEnd->format('H:i')}).",
            ];
        }
        
        return [
            'available' => true,
            'message' => 'Provider is available for this time slot.',
        ];
    }

    /**
     * Check if two time ranges overlap
     */
    protected function timesOverlap(string $time1Start, string $time1End, string $time2Start, string $time2End): bool
    {
        $t1Start = $this->parseTime($time1Start);
        $t1End = $this->parseTime($time1End);
        $t2Start = $this->parseTime($time2Start ?: '00:00');
        $t2End = $this->parseTime($time2End ?: '00:00');

        if (!$t1Start || !$t1End || !$t2Start || !$t2End) {
            return false;
        }
        
        return !($t1End->lte($t2Start) || $t1Start->gte($t2End));
    }

    protected function parseTime(string $value): ?Carbon
    {
        foreach (['H:i', 'H:i:s'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable $e) {
                // Try next known format.
            }
        }

        return null;
    }

    /**
     * Get next available slot for a provider
     */
    public function getNextAvailableSlot(int $providerId): ?array
    {
        $slotService = new SlotGenerationService();
        $availableDates = $slotService->getAvailableDates($providerId, 30);
        
        foreach ($availableDates as $dateInfo) {
            $slots = $slotService->generateAvailableSlots($providerId, $dateInfo['date']);
            
            if ($slots->count() > 0) {
                return [
                    'date' => $dateInfo['date'],
                    'display_date' => $dateInfo['display'],
                    'first_slot' => $slots->first(),
                ];
            }
        }
        
        return null;
    }
}

<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ProviderAvailability;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SlotGenerationService
{
    /**
     * Duration for each time slot in minutes
     */
    protected int $slotDuration = 60;

    /**
     * Generate available time slots for a provider on a specific date
     * 
     * @param int $providerId
     * @param string $date (format: YYYY-MM-DD)
     * @param int $slotDurationMinutes
     * @return Collection of available slots with times
     */
    public function generateAvailableSlots(int $providerId, string $date, int $slotDurationMinutes = 60): Collection
    {
        $this->slotDuration = $slotDurationMinutes;
        $dateObj = Carbon::parse($date);
        
        // Get provider's availability for this day of week
        $dayName = $dateObj->format('l'); // e.g., "Monday"
        
        $availability = ProviderAvailability::where('user_id', $providerId)
            ->where('day_of_week', $dayName)
            ->where('is_available', true)
            ->first();
        
        if (!$availability) {
            return collect(); // Provider not available on this day
        }

        // Generate all possible slots for this day
        $slots = $this->generateAllSlots($availability->start_time, $availability->end_time);
        
        // Filter out booked slots
        $availableSlots = $this->filterOutBookedSlots($providerId, $date, $slots);
        
        return $availableSlots;
    }

    /**
     * Generate all possible time slots between start and end time
     */
    protected function generateAllSlots(string $startTime, string $endTime): Collection
    {
        $slots = collect();
        $start = Carbon::createFromFormat('H:i:s', $startTime);
        $end = Carbon::createFromFormat('H:i:s', $endTime);
        
        while ($start->copy()->addMinutes($this->slotDuration)->lte($end)) {
            $slotEnd = $start->copy()->addMinutes($this->slotDuration);
            
            $slots->push([
                'time_from' => $start->format('H:i'),
                'time_to' => $slotEnd->format('H:i'),
                'display' => $start->format('h:i A') . ' - ' . $slotEnd->format('h:i A'),
            ]);
            
            $start->addMinutes($this->slotDuration);
        }
        
        return $slots;
    }

    /**
     * Filter out slots that have existing bookings
     */
    protected function filterOutBookedSlots(int $providerId, string $date, Collection $slots): Collection
    {
        // Get all bookings for this provider on this date (excluding cancelled)
        $bookedSlots = Booking::where('provider_id', $providerId)
            ->where('booking_date', $date)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->get(['time_from', 'time_to']);
        
        return $slots->filter(function ($slot) use ($bookedSlots) {
            foreach ($bookedSlots as $booking) {
                if ($this->slotsOverlap($slot['time_from'], $slot['time_to'], 
                    $booking->time_from, $booking->time_to)) {
                    return false; // This slot overlaps with a booking
                }
            }
            return true; // Slot is available
        })->values();
    }

    /**
     * Check if two time ranges overlap
     */
    protected function slotsOverlap(string $slot1Start, string $slot1End, string $slot2Start, string $slot2End): bool
    {
        $s1Start = Carbon::createFromFormat('H:i', $slot1Start);
        $s1End = Carbon::createFromFormat('H:i', $slot1End);
        $s2Start = Carbon::createFromFormat('H:i', $slot2Start);
        $s2End = Carbon::createFromFormat('H:i', $slot2End);
        
        return !($s1End->lte($s2Start) || $s1Start->gte($s2End));
    }

    /**
     * Get available dates for next N days
     */
    public function getAvailableDates(int $providerId, int $daysAhead = 30): Collection
    {
        $availableDates = collect();
        $today = Carbon::now();
        
        for ($i = 1; $i <= $daysAhead; $i++) {
            $date = $today->copy()->addDays($i);
            $dayName = $date->format('l');
            
            // Check if provider has availability for this day
            $hasAvailability = ProviderAvailability::where('user_id', $providerId)
                ->where('day_of_week', $dayName)
                ->where('is_available', true)
                ->exists();
            
            if ($hasAvailability) {
                $availableDates->push([
                    'date' => $date->format('Y-m-d'),
                    'display' => $date->format('D, M d, Y'),
                    'day' => $dayName,
                ]);
            }
        }
        
        return $availableDates;
    }
}

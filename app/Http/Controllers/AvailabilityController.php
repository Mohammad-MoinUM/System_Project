<?php

namespace App\Http\Controllers;

use App\Models\ProviderAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    /**
     * Show the availability management page
     */
    public function index()
    {
        $provider = Auth::user();
        
        // Get current availabilities
        $availabilities = ProviderAvailability::where('user_id', $provider->id)
            ->orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->get();
        
        // Days of week for form
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        // Initialize default availabilities if none exist
        if ($availabilities->isEmpty()) {
            foreach ($daysOfWeek as $day) {
                ProviderAvailability::create([
                    'user_id' => $provider->id,
                    'day_of_week' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'is_available' => in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']) ? true : false,
                ]);
            }
            
            $availabilities = ProviderAvailability::where('user_id', $provider->id)
                ->orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
                ->get();
        }
        
        return view('provider.availability.index', [
            'availabilities' => $availabilities,
            'daysOfWeek' => $daysOfWeek,
        ]);
    }

    /**
     * Update availability for a specific day
     */
    public function update(Request $request, $availabilityId)
    {
        $provider = Auth::user();
        $availability = ProviderAvailability::where('id', $availabilityId)
            ->where('user_id', $provider->id)
            ->firstOrFail();
        
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_available' => 'boolean',
        ]);
        
        // Convert time format to match database storage
        $validated['start_time'] = $validated['start_time'] . ':00';
        $validated['end_time'] = $validated['end_time'] . ':00';
        
        $availability->update($validated);
        
        return redirect()->route('provider.availability.index')
            ->with('success', "{$availability->day_of_week} availability updated successfully!");
    }

    /**
     * Update multiple availabilities at once
     */
    public function updateBatch(Request $request)
    {
        $provider = Auth::user();
        
        $validated = $request->validate([
            'availabilities' => 'required|array',
            'availabilities.*.id' => 'required|exists:provider_availabilities,id',
            'availabilities.*.start_time' => 'nullable|date_format:H:i',
            'availabilities.*.end_time' => 'nullable|date_format:H:i',
            'availabilities.*.is_available' => 'boolean',
        ]);
        
        foreach ($validated['availabilities'] as $availData) {
            $availability = ProviderAvailability::where('id', $availData['id'])
                ->where('user_id', $provider->id)
                ->firstOrFail();
            
            $updateData = [
                'is_available' => $availData['is_available'] ?? false,
            ];
            
            // Only update times if they're provided
            if (!empty($availData['start_time'])) {
                $updateData['start_time'] = $availData['start_time'] . ':00';
            }
            
            if (!empty($availData['end_time'])) {
                $updateData['end_time'] = $availData['end_time'] . ':00';
            }
            
            $availability->update($updateData);
        }
        
        return redirect()->route('provider.availability.index')
            ->with('success', 'All availabilities updated successfully!');
    }

    /**
     * Quick toggle availability for a day
     */
    public function toggle($availabilityId)
    {
        $provider = Auth::user();
        $availability = ProviderAvailability::where('id', $availabilityId)
            ->where('user_id', $provider->id)
            ->firstOrFail();
        
        $availability->update([
            'is_available' => !$availability->is_available,
        ]);
        
        $status = $availability->is_available ? 'available' : 'unavailable';
        
        return back()->with('success', "{$availability->day_of_week} marked as {$status}!");
    }

    /**
     * Get available slots for a specific date (AJAX endpoint)
     */
    public function getSlots(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:users,id',
            'date' => 'required|date_format:Y-m-d',
            'slot_duration' => 'nullable|integer|min:30|max:240',
        ]);
        
        $slotDuration = $validated['slot_duration'] ?? 60;
        
        $slotService = new \App\Services\SlotGenerationService();
        $slots = $slotService->generateAvailableSlots(
            $validated['provider_id'],
            $validated['date'],
            $slotDuration
        );
        
        return response()->json([
            'success' => true,
            'slots' => $slots,
            'count' => $slots->count(),
        ]);
    }

    /**
     * Get available dates for next N days (AJAX endpoint)
     */
    public function getAvailableDates(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:users,id',
            'days_ahead' => 'nullable|integer|min:1|max:90',
        ]);
        
        $daysAhead = $validated['days_ahead'] ?? 30;
        
        $slotService = new \App\Services\SlotGenerationService();
        $dates = $slotService->getAvailableDates($validated['provider_id'], $daysAhead);
        
        return response()->json([
            'success' => true,
            'dates' => $dates,
            'count' => $dates->count(),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Show booking creation form for a specific service.
     */
    public function create(Service $service): View
    {
        $service->load('provider:id,first_name,last_name,name,photo,city,area,bio,expertise,experience_years');

        // Get available dates for provider
        $slotService = new \App\Services\SlotGenerationService();
        $availableDates = $slotService->getAvailableDates($service->provider_id, 30);

        return view('pages.booking_create', compact('service', 'availableDates'));
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id'              => 'required|exists:services,id',
            'scheduled_at'            => 'nullable|date|after:now',
            'booking_date'            => 'nullable|date|after_or_equal:today',
            'time_from'               => 'nullable|date_format:H:i',
            'time_to'                 => 'nullable|date_format:H:i|after:time_from',
            'slot_duration_minutes'   => 'nullable|integer|min:30|max:240',
            'notes'                   => 'nullable|string|max:1000',
        ]);

        $service = Service::findOrFail($validated['service_id']);

        // Prevent booking own service
        if ($service->provider_id === Auth::id()) {
            return back()->withErrors(['service_id' => 'You cannot book your own service.']);
        }

        // If slot-based booking (new system)
        if (!empty($validated['booking_date']) && !empty($validated['time_from']) && !empty($validated['time_to'])) {
            // Validate slot availability
            $conflictService = new \App\Services\BookingConflictService();
            
            // Check availability in provider schedule
            $availCheck = $conflictService->isProviderAvailable(
                $service->provider_id,
                $validated['booking_date'],
                $validated['time_from'],
                $validated['time_to']
            );
            
            if (!$availCheck['available']) {
                return back()->withErrors(['time_from' => $availCheck['message']]);
            }
            
            // Check for conflicts with existing bookings
            $conflictCheck = $conflictService->checkConflict(
                $service->provider_id,
                $validated['booking_date'],
                $validated['time_from'],
                $validated['time_to']
            );
            
            if ($conflictCheck['conflicts']) {
                return back()->withErrors(['time_from' => $conflictCheck['message']]);
            }
            
            $booking = Booking::create([
                'service_id'           => $service->id,
                'taker_id'             => Auth::id(),
                'provider_id'          => $service->provider_id,
                'status'               => 'pending',
                'booking_date'         => $validated['booking_date'],
                'time_from'            => $validated['time_from'],
                'time_to'              => $validated['time_to'],
                'slot_duration_minutes' => $validated['slot_duration_minutes'] ?? 60,
                'total'                => $service->price ?? 0,
                'notes'                => $validated['notes'],
            ]);
        } else {
            // Fall back to old system (scheduled_at)
            $booking = Booking::create([
                'service_id'   => $service->id,
                'taker_id'     => Auth::id(),
                'provider_id'  => $service->provider_id,
                'status'       => 'pending',
                'scheduled_at' => $validated['scheduled_at'] ?? now(),
                'total'        => $service->price ?? 0,
                'notes'        => $validated['notes'],
            ]);
        }

        return redirect()->route('booking.show', $booking)
                         ->with('success', 'Booking placed successfully! The provider will be notified.');
    }

    /**
     * Show booking details.
     */
    public function show(Booking $booking): View
    {
        // Only the customer or provider of this booking may view it
        $user = Auth::user();
        if ($booking->taker_id !== $user->id && $booking->provider_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $booking->load(['service', 'provider', 'taker', 'reviews']);

        return view('pages.booking_show', compact('booking'));
    }

    /**
     * Provider accepts a booking.
     */
    public function accept(Booking $booking): RedirectResponse
    {
        if ($booking->provider_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return back()->with('error', 'This booking can no longer be accepted.');
        }

        $booking->update(['status' => 'active']);

        return back()->with('success', 'Booking accepted.');
    }

    /**
     * Provider rejects a booking.
     */
    public function reject(Booking $booking): RedirectResponse
    {
        if ($booking->provider_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return back()->with('error', 'This booking can no longer be rejected.');
        }

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking rejected.');
    }

    /**
     * Provider marks a booking as in-progress.
     */
    public function start(Booking $booking): RedirectResponse
    {
        if ($booking->provider_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->status !== 'active') {
            return back()->with('error', 'This booking must be active to start.');
        }

        $booking->update(['status' => 'in_progress']);

        return back()->with('success', 'Booking marked as in progress.');
    }

    /**
     * Provider marks a booking as completed.
     */
    public function complete(Booking $booking): RedirectResponse
    {
        if ($booking->provider_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($booking->status, ['active', 'in_progress'])) {
            return back()->with('error', 'This booking cannot be completed.');
        }

        $booking->update(['status' => 'completed']);

        return back()->with('success', 'Booking marked as completed.');
    }

    /**
     * Customer cancels a booking.
     */
    public function cancel(Booking $booking): RedirectResponse
    {
        if ($booking->taker_id !== Auth::id()) {
            abort(403);
        }

        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'This booking cannot be cancelled.');
        }

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled.');
    }
}

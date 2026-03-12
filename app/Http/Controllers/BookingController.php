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

        return view('pages.booking_create', compact('service'));
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id'   => 'required|exists:services,id',
            'scheduled_at' => 'required|date|after:now',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $service = Service::findOrFail($validated['service_id']);

        // Prevent booking own service
        if ($service->provider_id === Auth::id()) {
            return back()->withErrors(['service_id' => 'You cannot book your own service.']);
        }

        $booking = Booking::create([
            'service_id'   => $service->id,
            'taker_id'     => Auth::id(),
            'provider_id'  => $service->provider_id,
            'status'       => 'pending',
            'scheduled_at' => $validated['scheduled_at'],
            'total'        => $service->price ?? 0,
            'notes'        => $validated['notes'],
        ]);

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

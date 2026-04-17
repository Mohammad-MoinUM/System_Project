<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\UserAddress;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\LoyaltyRewardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Show booking creation form for a specific service.
     */
    public function create(Service $service): View
    {
        $service->load('provider:id,first_name,last_name,name,photo,city,area,bio,expertise,experience_years');
        $customer = Auth::user();

        // Get available dates for provider
        $slotService = new \App\Services\SlotGenerationService();
        $availableDates = $slotService->getAvailableDates($service->provider_id, 30);

        $customerAddresses = $customer->addresses()->orderByDesc('is_default')->orderBy('label')->get();

        $bundleServices = Service::where('provider_id', $service->provider_id)
            ->where('is_active', true)
            ->where('id', '!=', $service->id)
            ->orderBy('name')
            ->get();

        return view('pages.booking_create', compact('service', 'availableDates', 'bundleServices', 'customerAddresses', 'customer'));
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id'              => 'required|exists:services,id',
            'booking_mode'            => 'required|in:instant,scheduled',
            'scheduled_at'            => 'nullable|date|after:now',
            'booking_date'            => 'nullable|date|after_or_equal:today',
            'time_from'               => 'nullable|date_format:H:i',
            'time_to'                 => 'nullable|date_format:H:i|after:time_from',
            'slot_duration_minutes'   => 'nullable|integer|min:30|max:240',
            'recurrence_type'         => 'nullable|in:none,weekly,monthly',
            'recurrence_interval'     => 'nullable|integer|min:1|max:12',
            'recurrence_end_date'     => 'nullable|date|after_or_equal:today',
            'extra_service_ids'        => 'nullable|array',
            'extra_service_ids.*'      => 'integer|exists:services,id',
            'attachments'             => 'nullable|array',
            'attachments.*'            => 'file|mimes:jpg,jpeg,png,webp,mp4,mov,avi,mkv|max:10240',
            'service_address_source'  => 'required|in:saved,manual',
            'saved_address_id'        => 'nullable|exists:user_addresses,id',
            'service_address_label'   => 'nullable|string|max:50',
            'service_address_line1'   => 'nullable|string|max:255',
            'service_address_line2'   => 'nullable|string|max:255',
            'service_city'            => 'nullable|string|max:255',
            'service_area'            => 'nullable|string|max:255',
            'service_postal_code'     => 'nullable|string|max:50',
            'payment_method'           => 'required|in:bkash,nagad,card,cash,wallet',
            'payment_split_type'       => 'required|in:full,partial',
            'upfront_percent'          => 'nullable|integer|min:10|max:100',
            'notes'                   => 'nullable|string|max:1000',
        ]);

        $service = Service::findOrFail($validated['service_id']);

        // Prevent booking own service
        if ($service->provider_id === Auth::id()) {
            return back()->withErrors(['service_id' => 'You cannot book your own service.']);
        }

        if ($validated['booking_mode'] === 'scheduled') {
            if (empty($validated['booking_date']) || empty($validated['time_from']) || empty($validated['time_to'])) {
                return back()->withErrors([
                    'booking_date' => 'Please choose a future date and time slot for scheduled bookings.',
                ]);
            }
        }

        $extraServices = collect();
        if (!empty($validated['extra_service_ids'])) {
            $extraServices = Service::where('provider_id', $service->provider_id)
                ->where('is_active', true)
                ->whereIn('id', $validated['extra_service_ids'])
                ->get();

            if ($extraServices->count() !== count($validated['extra_service_ids'])) {
                return back()->withErrors(['extra_service_ids' => 'One or more selected add-on services are not available.']);
            }
        }

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('bookings/attachments', 'public');
                $attachments[] = [
                    'path' => $path,
                    'type' => $file->getClientMimeType(),
                    'original_name' => $file->getClientOriginalName(),
                ];
            }
        }

        $baseTotal = (float) ($service->price ?? 0);
        $extraTotal = (float) $extraServices->sum(fn (Service $extraService) => (float) ($extraService->price ?? 0));
        $total = $baseTotal + $extraTotal;

        $upfrontPercent = $validated['payment_split_type'] === 'partial'
            ? ($validated['upfront_percent'] ?? 30)
            : 100;

        $upfrontAmount = round($total * ($upfrontPercent / 100), 2);
        $remainingAmount = round(max($total - $upfrontAmount, 0), 2);

        $recurrenceType = $validated['recurrence_type'] ?? null;
        if ($recurrenceType === 'none') {
            $recurrenceType = null;
        }

        $bookingData = [
            'service_id' => $service->id,
            'taker_id' => Auth::id(),
            'provider_id' => $service->provider_id,
            'status' => 'pending',
            'booking_mode' => $validated['booking_mode'],
            'recurrence_type' => $recurrenceType,
            'recurrence_interval' => $validated['recurrence_interval'] ?? 1,
            'recurrence_end_date' => $validated['recurrence_end_date'] ?? null,
            'extra_service_ids' => $extraServices->pluck('id')->values()->all() ?: null,
            'total' => $total,
            'notes' => $validated['notes'] ?? null,
            'attachments' => $attachments ?: null,
            'tracking_status' => 'not_started',
            'payment_method' => $validated['payment_method'],
            'payment_split_type' => $validated['payment_split_type'],
            'upfront_amount' => $validated['payment_method'] === 'cash' ? 0 : $upfrontAmount,
            'remaining_amount' => $validated['payment_method'] === 'cash' ? $total : $remainingAmount,
            'payment_status' => $validated['payment_method'] === 'cash' ? 'cash_due' : (($validated['payment_split_type'] === 'partial' && $upfrontAmount < $total) ? 'unpaid' : 'unpaid'),
            'cashback_amount' => round($total * 0.05, 2),
        ];

        if ($validated['service_address_source'] === 'saved') {
            $savedAddress = UserAddress::where('id', $validated['saved_address_id'] ?? null)
                ->where('user_id', Auth::id())
                ->first();

            if (!$savedAddress) {
                return back()->withErrors(['saved_address_id' => 'Please select one of your saved addresses.']);
            }

            $bookingData['service_address_label'] = $savedAddress->label;
            $bookingData['service_address_line1'] = $savedAddress->line1;
            $bookingData['service_address_line2'] = $savedAddress->line2;
            $bookingData['service_city'] = $savedAddress->city;
            $bookingData['service_area'] = $savedAddress->area;
            $bookingData['service_postal_code'] = $savedAddress->postal_code;
        } else {
            $manualAddressRules = [
                'service_address_label' => 'required|string|max:50',
                'service_address_line1' => 'required|string|max:255',
                'service_address_line2' => 'nullable|string|max:255',
                'service_city' => 'nullable|string|max:255',
                'service_area' => 'nullable|string|max:255',
                'service_postal_code' => 'nullable|string|max:50',
            ];

            $request->validate($manualAddressRules);

            $bookingData['service_address_label'] = $validated['service_address_label'];
            $bookingData['service_address_line1'] = $validated['service_address_line1'];
            $bookingData['service_address_line2'] = $validated['service_address_line2'] ?? null;
            $bookingData['service_city'] = $validated['service_city'] ?? null;
            $bookingData['service_area'] = $validated['service_area'] ?? null;
            $bookingData['service_postal_code'] = $validated['service_postal_code'] ?? null;
        }

        // If scheduled booking, validate slot availability
        if ($validated['booking_mode'] === 'scheduled' && !empty($validated['booking_date']) && !empty($validated['time_from']) && !empty($validated['time_to'])) {
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
            
            $bookingData['scheduled_at'] = Carbon::createFromFormat(
                'Y-m-d H:i',
                $validated['booking_date'] . ' ' . $validated['time_from']
            );
            $bookingData['booking_date'] = $validated['booking_date'];
            $bookingData['time_from'] = $validated['time_from'];
            $bookingData['time_to'] = $validated['time_to'];
            $bookingData['slot_duration_minutes'] = $validated['slot_duration_minutes'] ?? 60;
        } else {
            $bookingData['scheduled_at'] = now();
            $bookingData['booking_date'] = now()->toDateString();
            $bookingData['time_from'] = now()->addMinutes(30)->format('H:i');
            $bookingData['time_to'] = now()->addMinutes(90)->format('H:i');
            $bookingData['slot_duration_minutes'] = $validated['slot_duration_minutes'] ?? 60;
        }

        $booking = Booking::create($bookingData);

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

        $booking->load([
            'service',
            'provider',
            'taker',
            'reviews',
            'payments',
            'complaints' => fn ($query) => $query->latest(),
        ]);

        $extraServices = collect();
        if (!empty($booking->extra_service_ids)) {
            $extraServices = Service::whereIn('id', $booking->extra_service_ids)->get();
        }

        $attachments = collect($booking->attachments ?? [])->map(function ($attachment) {
            return [
                'url' => asset('storage/' . ($attachment['path'] ?? '')),
                'type' => $attachment['type'] ?? null,
                'name' => $attachment['original_name'] ?? basename($attachment['path'] ?? ''),
            ];
        })->filter(fn ($attachment) => !empty($attachment['url']))->values();

        return view('pages.booking_show', compact('booking', 'extraServices', 'attachments'));
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

        $booking->update([
            'status' => 'active',
            'estimated_arrival_at' => $booking->estimated_arrival_at ?? ($booking->booking_mode === 'instant'
                ? now()->addMinutes(45)
                : ($booking->scheduled_at ?? now()->addMinutes(45))),
            'tracking_status' => 'en_route',
            'tracking_updated_at' => now(),
        ]);

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

        if (in_array($booking->payment_status, ['paid', 'partial_paid']) && !$booking->cashback_credited_at) {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $booking->taker_id],
                ['balance' => 0, 'cashback_balance' => 0]
            );

            $wallet->increment('balance', (float) $booking->cashback_amount);
            $wallet->increment('cashback_balance', (float) $booking->cashback_amount);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $booking->taker_id,
                'booking_id' => $booking->id,
                'type' => 'cashback',
                'amount' => (float) $booking->cashback_amount,
                'balance_after' => (float) $wallet->fresh()->balance,
                'description' => 'Cashback credited after service completion',
            ]);

            $booking->update(['cashback_credited_at' => now()]);
        }

        app(LoyaltyRewardService::class)->awardForCompletedBooking($booking);

        return back()->with('success', 'Booking marked as completed.');
    }

    /**
     * Customer cancels a booking.
     */
    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->taker_id !== Auth::id()) {
            abort(403);
        }

        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'This booking cannot be cancelled.');
        }

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Booking cancelled.');
    }

    /**
     * Rebook the same service again.
     */
    public function rebook(Booking $booking): RedirectResponse
    {
        if ($booking->taker_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->service->provider_id !== $booking->provider_id) {
            return back()->with('error', 'The original provider is no longer linked to this service.');
        }

        return redirect()
            ->route('booking.create', $booking->service)
            ->with('success', 'Booking form loaded. Confirm a new time to place the booking again.');
    }

    /**
     * Create a new booking instantly with the same provider and service.
     */
    public function rebookNow(Booking $booking): RedirectResponse
    {
        if ($booking->taker_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($booking->status, ['completed', 'cancelled'], true)) {
            return back()->with('error', 'You can only instantly rebook completed or cancelled bookings.');
        }

        $service = Service::where('id', $booking->service_id)
            ->where('provider_id', $booking->provider_id)
            ->where('is_active', true)
            ->first();

        if (!$service) {
            return back()->with('error', 'This provider service is no longer available for instant rebooking.');
        }

        $extraServices = collect();
        if (!empty($booking->extra_service_ids)) {
            $extraServices = Service::where('provider_id', $service->provider_id)
                ->where('is_active', true)
                ->whereIn('id', $booking->extra_service_ids)
                ->get();
        }

        $baseTotal = (float) ($service->price ?? 0);
        $extraTotal = (float) $extraServices->sum(fn (Service $extraService) => (float) ($extraService->price ?? 0));
        $total = $baseTotal + $extraTotal;

        $splitType = in_array($booking->payment_split_type, ['full', 'partial'], true)
            ? $booking->payment_split_type
            : 'full';
        $upfrontPercent = $splitType === 'partial' ? 30 : 100;
        $upfrontAmount = $booking->payment_method === 'cash' ? 0 : round($total * ($upfrontPercent / 100), 2);
        $remainingAmount = $booking->payment_method === 'cash' ? $total : round(max($total - $upfrontAmount, 0), 2);

        $slotDuration = max((int) ($booking->slot_duration_minutes ?? 60), 30);
        $timeFrom = now()->addMinutes(30);
        $timeTo = (clone $timeFrom)->addMinutes($slotDuration);

        $newBooking = Booking::create([
            'service_id' => $service->id,
            'taker_id' => Auth::id(),
            'provider_id' => $service->provider_id,
            'status' => 'pending',
            'booking_mode' => 'instant',
            'recurrence_type' => null,
            'recurrence_interval' => 1,
            'recurrence_end_date' => null,
            'extra_service_ids' => $extraServices->pluck('id')->values()->all() ?: null,
            'scheduled_at' => $timeFrom,
            'booking_date' => $timeFrom->toDateString(),
            'time_from' => $timeFrom->format('H:i'),
            'time_to' => $timeTo->format('H:i'),
            'slot_duration_minutes' => $slotDuration,
            'total' => $total,
            'notes' => trim((string) $booking->notes) !== ''
                ? 'Rebooked from #' . $booking->id . ': ' . $booking->notes
                : 'Rebooked from #' . $booking->id,
            'attachments' => null,
            'tracking_status' => 'not_started',
            'payment_method' => $booking->payment_method,
            'payment_split_type' => $splitType,
            'upfront_amount' => $upfrontAmount,
            'remaining_amount' => $remainingAmount,
            'payment_status' => 'unpaid',
            'cashback_amount' => round($total * 0.05, 2),
        ]);

        return redirect()
            ->route('booking.show', $newBooking)
            ->with('success', 'Rebook placed with the same provider. Waiting for provider confirmation.');
    }

    /**
     * Provider updates live tracking details.
     */
    public function updateTracking(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->provider_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'tracking_status' => ['required', 'in:not_started,en_route,arrived'],
            'provider_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'provider_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'eta_minutes' => ['nullable', 'integer', 'min:1', 'max:720'],
        ]);

        $booking->update([
            'tracking_status' => $data['tracking_status'],
            'provider_latitude' => $data['provider_latitude'] ?? $booking->provider_latitude,
            'provider_longitude' => $data['provider_longitude'] ?? $booking->provider_longitude,
            'tracking_updated_at' => now(),
            'estimated_arrival_at' => !empty($data['eta_minutes']) ? now()->addMinutes((int) $data['eta_minutes']) : $booking->estimated_arrival_at,
        ]);

        return back()->with('success', 'Tracking details updated.');
    }

    /**
     * Return tracking data for live refresh.
     */
    public function tracking(Booking $booking)
    {
        $user = Auth::user();
        if ($booking->taker_id !== $user->id && $booking->provider_id !== $user->id) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'tracking_status' => $booking->tracking_status,
            'estimated_arrival_at' => $booking->estimated_arrival_at?->toIso8601String(),
            'tracking_updated_at' => $booking->tracking_updated_at?->toIso8601String(),
            'provider_latitude' => $booking->provider_latitude,
            'provider_longitude' => $booking->provider_longitude,
        ]);
    }
}

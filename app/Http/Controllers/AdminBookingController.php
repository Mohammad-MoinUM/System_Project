<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\RefundRequest;
use App\Services\PlaceNameService;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AdminBookingController extends Controller
{
    /**
     * Show all bookings
     */
    public function index(Request $request, PlaceNameService $placeNameService): View
    {
        $query = Booking::with('taker', 'provider', 'service');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                    ->orWhereHas('taker', fn($sq) => $sq->where('name', 'like', "%$search%"))
                    ->orWhereHas('provider', fn($sq) => $sq->where('name', 'like', "%$search%"));
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);
        $bookings->getCollection()->transform(function (Booking $booking) use ($placeNameService) {
            if (!is_null($booking->provider_latitude) && !is_null($booking->provider_longitude)) {
                $booking->place_name = $placeNameService->getPlaceName(
                    $booking->provider_latitude,
                    $booking->provider_longitude
                );
            }

            return $booking;
        });

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'search' => $request->search ?? '',
            'status_filter' => $request->status ?? 'all',
            'statuses' => ['pending', 'active', 'in_progress', 'awaiting_confirmation', 'completed', 'cancelled'],
        ]);
    }

    /**
     * Show booking details
     */
    public function show(Booking $booking): View
    {
        $booking->load(['service', 'taker', 'provider', 'payments', 'refundRequests.user', 'refundRequests.reviewer']);

        return view('admin.bookings.show', ['booking' => $booking]);
    }

    /**
     * Cancel booking (admin action)
     */
    public function cancel(Booking $booking): RedirectResponse
    {
        $booking->update(['status' => 'cancelled']);

        return redirect()->route('admin.bookings.show', $booking)->with('success', 'Booking cancelled.');
    }

    public function approveRefund(RefundRequest $refundRequest): RedirectResponse
    {
        $refundRequest->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        return redirect()->route('admin.bookings.show', $refundRequest->booking)->with('success', 'Refund approved.');
    }

    public function rejectRefund(Request $request, RefundRequest $refundRequest): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        $refundRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        return redirect()->route('admin.bookings.show', $refundRequest->booking)->with('success', 'Refund rejected.');
    }
}

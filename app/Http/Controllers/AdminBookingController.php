<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\RefundRequest;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AdminBookingController extends Controller
{
    /**
     * Show all bookings
     */
    public function index(Request $request): View
    {
        $query = Booking::with('taker', 'provider', 'service');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('id', 'like', "%$search%")
                  ->orWhereHas('taker', fn($q) => $q->where('name', 'like', "%$search%"))
                  ->orWhereHas('provider', fn($q) => $q->where('name', 'like', "%$search%"));
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'search' => $request->search ?? '',
            'status_filter' => $request->status ?? 'all',
            'statuses' => ['pending', 'accepted', 'started', 'completed', 'cancelled'],
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

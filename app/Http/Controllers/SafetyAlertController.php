<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\SafetyAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SafetyAlertController extends Controller
{
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $user = Auth::user();

        if ((int) $booking->taker_id !== (int) $user->id && (int) $booking->provider_id !== (int) $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        SafetyAlert::create([
            'booking_id' => $booking->id,
            'triggered_by_user_id' => $user->id,
            'user_role' => $user->role,
            'message' => $validated['message'] ?? null,
            'status' => 'open',
            'triggered_at' => now(),
        ]);

        $booking->update(['sos_triggered' => true]);

        return back()->with('success', 'Safety alert sent. Our support team has been notified.');
    }
}

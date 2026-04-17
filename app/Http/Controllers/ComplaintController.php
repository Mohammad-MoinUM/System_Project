<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    /**
     * Submit a formal complaint for a booking.
     */
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->taker_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($booking->status, ['completed', 'cancelled'], true)) {
            return back()->with('error', 'Complaints can be filed after the booking is completed or cancelled.');
        }

        $existingOpenComplaint = Complaint::where('booking_id', $booking->id)
            ->where('user_id', Auth::id())
            ->whereIn('status', ['submitted', 'in_review'])
            ->exists();

        if ($existingOpenComplaint) {
            return back()->with('error', 'You already have an active complaint for this booking.');
        }

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'details' => ['required', 'string', 'min:20', 'max:3000'],
            'evidence' => ['nullable', 'array', 'max:5'],
            'evidence.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf,mp4,mov,avi,mkv', 'max:10240'],
        ]);

        $evidencePaths = [];
        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence') as $evidenceFile) {
                $evidencePaths[] = [
                    'path' => $evidenceFile->store('complaints/evidence', 'public'),
                    'name' => $evidenceFile->getClientOriginalName(),
                    'type' => $evidenceFile->getClientMimeType(),
                ];
            }
        }

        Complaint::create([
            'booking_id' => $booking->id,
            'user_id' => Auth::id(),
            'provider_id' => $booking->provider_id,
            'subject' => $validated['subject'],
            'details' => $validated['details'],
            'evidence_paths' => $evidencePaths ?: null,
            'status' => 'submitted',
        ]);

        return back()->with('success', 'Complaint filed successfully. Our team will review it shortly.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingChatMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingChatController extends Controller
{
    public function show(Booking $booking): View
    {
        $this->authorizeParticipant($booking);

        $messages = BookingChatMessage::where('booking_id', $booking->id)
            ->with('sender:id,name,photo')
            ->orderBy('created_at')
            ->get();

        BookingChatMessage::where('booking_id', $booking->id)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('pages.booking_chat', compact('booking', 'messages'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeParticipant($booking);

        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:3000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],
        ]);

        if (empty($data['message']) && !$request->hasFile('attachment')) {
            return back()->with('error', 'Write a message or attach a file.');
        }

        BookingChatMessage::create([
            'booking_id' => $booking->id,
            'sender_id' => Auth::id(),
            'message' => $data['message'] ?? null,
            'attachment_path' => $request->file('attachment')?->store('booking-chat', 'public'),
            'is_read' => false,
        ]);

        return back()->with('success', 'Message sent.');
    }

    private function authorizeParticipant(Booking $booking): void
    {
        $userId = Auth::id();
        if ($booking->provider_id !== $userId && $booking->taker_id !== $userId) {
            abort(403);
        }
    }
}

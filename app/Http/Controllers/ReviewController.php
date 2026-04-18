<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a new review for a completed booking.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'punctuality_rating' => 'required|integer|min:1|max:5',
            'quality_rating' => 'required|integer|min:1|max:5',
            'behavior_rating' => 'required|integer|min:1|max:5',
            'value_rating' => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:2000',
        ]);

        $booking = \App\Models\Booking::findOrFail($validated['booking_id']);

        // Only the customer can review
        if ($booking->taker_id !== Auth::id()) {
            abort(403);
        }

        // Must be completed
        if ($booking->status !== 'completed') {
            return back()->with('error', 'You can only review completed bookings.');
        }

        // Prevent duplicate reviews
        $existingReview = Review::where('booking_id', $booking->id)
            ->where('taker_id', Auth::id())
            ->first();

        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this booking.');
        }

        $dimensionAverage = (int) round(collect([
            (int) $validated['punctuality_rating'],
            (int) $validated['quality_rating'],
            (int) $validated['behavior_rating'],
            (int) $validated['value_rating'],
        ])->avg());

        Review::create([
            'booking_id'  => $booking->id,
            'provider_id' => $booking->provider_id,
            'taker_id'    => Auth::id(),
            'rating'      => $dimensionAverage,
            'punctuality_rating' => $validated['punctuality_rating'],
            'quality_rating' => $validated['quality_rating'],
            'behavior_rating' => $validated['behavior_rating'],
            'value_rating' => $validated['value_rating'],
            'comment'     => $validated['comment'],
        ]);

        return back()->with('success', 'Review submitted successfully.');
    }

    /**
     * Store a reply to a review (provider only).
     */
    public function reply(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        // Only the provider for this review can reply
        if ($review->provider_id !== Auth::id()) {
            abort(403);
        }

        // Limit one reply per review per user
        $existing = ReviewReply::where('review_id', $review->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            return back()->with('error', 'You have already replied to this review.');
        }

        ReviewReply::create([
            'review_id' => $review->id,
            'user_id'   => Auth::id(),
            'comment'   => $validated['comment'],
        ]);

        return back()->with('success', 'Reply posted.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class AdminReviewController extends Controller
{
    /**
     * Show all reviews
     */
    public function index(Request $request): View
    {
        $query = Review::with('taker', 'provider');

        if ($request->filled('rating') && $request->rating !== 'all') {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('comment', 'like', "%$search%")
                  ->orWhereHas('provider', fn($q) => $q->where('name', 'like', "%$search%"));
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'search' => $request->search ?? '',
            'rating_filter' => $request->rating ?? 'all',
        ]);
    }

    /**
     * Show review details
     */
    public function show(Review $review): View
    {
        return view('admin.reviews.show', ['review' => $review]);
    }

    /**
     * Delete review
     */
    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted.');
    }
}

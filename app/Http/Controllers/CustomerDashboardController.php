<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Review;
use App\Models\SavedProvider;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        // Active bookings
        $activeBookings = Booking::where('taker_id', $user->id)
            ->where('status', 'active')
            ->with('service')
            ->get();

        // Recent completed bookings
        $recentHistory = Booking::where('taker_id', $user->id)
            ->where('status', 'completed')
            ->with('service')
            ->latest()
            ->take(5)
            ->get();

        // Stats
        $totalSpent = Booking::where('taker_id', $user->id)
            ->where('status', 'completed')
            ->sum('total');

        $servicesUsed = Booking::where('taker_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $savedProviders = SavedProvider::where('taker_id', $user->id)->count();

        // Popular services
        $popularServices = Service::withCount('bookings')
            ->orderByDesc('bookings_count')
            ->take(6)
            ->get();

        // Reviews
        $reviews = Review::where('taker_id', $user->id)
            ->with(['provider', 'booking.service'])
            ->latest()
            ->get();

        return view('pages.customer_dashboard', compact(
            'activeBookings',
            'recentHistory',
            'totalSpent',
            'servicesUsed',
            'savedProviders',
            'popularServices',
            'reviews'
        ));
    }

    public function browse(): RedirectResponse
    {
        return redirect()->route('customer.dashboard');
    }

    public function history(): RedirectResponse
    {
        return redirect()->route('customer.dashboard');
    }
}
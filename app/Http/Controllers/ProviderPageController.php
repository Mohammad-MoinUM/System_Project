<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProviderPageController extends Controller
{
    public function jobs(): View
    {
        $providerId = Auth::id();

        $bookings = Booking::where('provider_id', $providerId)
            ->with(['service', 'taker:id,first_name,last_name,name,phone,city,area'])
            ->latest()
            ->paginate(15);

        $counts = [
            'pending'    => Booking::where('provider_id', $providerId)->where('status', 'pending')->count(),
            'active'     => Booking::where('provider_id', $providerId)->where('status', 'active')->count(),
            'in_progress'=> Booking::where('provider_id', $providerId)->where('status', 'in_progress')->count(),
            'completed'  => Booking::where('provider_id', $providerId)->where('status', 'completed')->count(),
            'cancelled'  => Booking::where('provider_id', $providerId)->where('status', 'cancelled')->count(),
        ];

        return view('pages.provider.jobs', compact('bookings', 'counts'));
    }

    public function earnings(): View
    {
        $providerId = Auth::id();
        $now = Carbon::now();

        $todayEarnings = (float) Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->whereDate('updated_at', $now->toDateString())
            ->sum('total');

        $weekEarnings = (float) Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->where('updated_at', '>=', $now->copy()->subDays(7))
            ->sum('total');

        $monthEarnings = (float) Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->where('updated_at', '>=', $now->copy()->subDays(30))
            ->sum('total');

        $totalEarnings = (float) Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->sum('total');

        $recentTransactions = Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->with('service', 'taker:id,name')
            ->latest('updated_at')
            ->take(20)
            ->get();

        return view('pages.provider.earnings', compact(
            'todayEarnings', 'weekEarnings', 'monthEarnings', 'totalEarnings', 'recentTransactions'
        ));
    }

    public function reviews(): View
    {
        $providerId = Auth::id();

        $reviews = Review::where('provider_id', $providerId)
            ->with(['taker:id,first_name,last_name,name,photo', 'booking.service', 'replies.user:id,name'])
            ->latest()
            ->paginate(10);

        $avgRating = Review::where('provider_id', $providerId)->avg('rating');
        $totalReviews = Review::where('provider_id', $providerId)->count();

        $ratingDistribution = Review::where('provider_id', $providerId)
            ->select('rating', DB::raw('COUNT(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        return view('pages.provider.reviews', compact('reviews', 'avgRating', 'totalReviews', 'ratingDistribution'));
    }

    public function schedule(): View
    {
        $providerId = Auth::id();

        $upcomingBookings = Booking::where('provider_id', $providerId)
            ->whereIn('status', ['pending', 'active', 'in_progress'])
            ->with(['service', 'taker:id,first_name,last_name,name,phone'])
            ->orderBy('scheduled_at')
            ->get();

        return view('pages.provider.schedule', compact('upcomingBookings'));
    }

    public function analytics(): View
    {
        $providerId = Auth::id();
        $now = Carbon::now();

        // Monthly earnings for last 6 months
        $monthlyEarnings = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $earnings = (float) Booking::where('provider_id', $providerId)
                ->where('status', 'completed')
                ->whereYear('updated_at', $month->year)
                ->whereMonth('updated_at', $month->month)
                ->sum('total');

            $monthlyEarnings[] = [
                'month' => $month->format('M Y'),
                'amount' => $earnings,
            ];
        }

        $totalBookings = Booking::where('provider_id', $providerId)->count();
        $completedBookings = Booking::where('provider_id', $providerId)->where('status', 'completed')->count();
        $cancelledBookings = Booking::where('provider_id', $providerId)->where('status', 'cancelled')->count();

        $avgRating = Review::where('provider_id', $providerId)->avg('rating');
        $totalReviews = Review::where('provider_id', $providerId)->count();
        $uniqueClients = Booking::where('provider_id', $providerId)->distinct('taker_id')->count('taker_id');

        return view('pages.provider.analytics', compact(
            'monthlyEarnings', 'totalBookings', 'completedBookings', 'cancelledBookings',
            'avgRating', 'totalReviews', 'uniqueClients'
        ));
    }

    public function settings(): View
    {
        $user = Auth::user();
        return view('pages.provider.settings', compact('user'));
    }
}

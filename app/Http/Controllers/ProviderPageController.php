<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProviderPageController extends Controller
{
    public function jobs(): View
    {
        $providerId = Auth::id();

        $cacheKey = 'provider.jobs:' . $providerId . ':' . request()->get('page', 1);
        $payload = Cache::remember($cacheKey, now()->addSeconds(45), function () use ($providerId) {
            $bookings = Booking::where('provider_id', $providerId)
                ->with(['service', 'taker:id,first_name,last_name,name,phone,city,area'])
                ->latest()
                ->paginate(15);

            $statusCounts = Booking::where('provider_id', $providerId)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            $counts = [
                'pending' => (int) ($statusCounts['pending'] ?? 0),
                'active' => (int) ($statusCounts['active'] ?? 0),
                'in_progress' => (int) ($statusCounts['in_progress'] ?? 0),
                'awaiting_confirmation' => (int) ($statusCounts['awaiting_confirmation'] ?? 0),
                'completed' => (int) ($statusCounts['completed'] ?? 0),
                'cancelled' => (int) ($statusCounts['cancelled'] ?? 0),
            ];

            return compact('bookings', 'counts');
        });

        return view('pages.provider.jobs', $payload);
    }

    public function earnings(): View
    {
        $providerId = Auth::id();
        $now = Carbon::now();

        $cacheKey = 'provider.earnings:' . $providerId . ':' . $now->format('Y-m-d-H');
        $payload = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($providerId, $now) {
            $todayBookingEarnings = (float) Booking::where('provider_id', $providerId)
                ->where('status', 'completed')
                ->whereDate('updated_at', $now->toDateString())
                ->sum('total');

            $weekBookingEarnings = (float) Booking::where('provider_id', $providerId)
                ->where('status', 'completed')
                ->where('updated_at', '>=', $now->copy()->subDays(7))
                ->sum('total');

            $monthBookingEarnings = (float) Booking::where('provider_id', $providerId)
                ->where('status', 'completed')
                ->where('updated_at', '>=', $now->copy()->subDays(30))
                ->sum('total');

            $totalBookingEarnings = (float) Booking::where('provider_id', $providerId)
                ->where('status', 'completed')
                ->sum('total');

            $todayTipEarnings = (float) WalletTransaction::where('user_id', $providerId)
                ->where('type', 'tip_credit')
                ->whereDate('created_at', $now->toDateString())
                ->sum('amount');

            $weekTipEarnings = (float) WalletTransaction::where('user_id', $providerId)
                ->where('type', 'tip_credit')
                ->where('created_at', '>=', $now->copy()->subDays(7))
                ->sum('amount');

            $monthTipEarnings = (float) WalletTransaction::where('user_id', $providerId)
                ->where('type', 'tip_credit')
                ->where('created_at', '>=', $now->copy()->subDays(30))
                ->sum('amount');

            $totalTipEarnings = (float) WalletTransaction::where('user_id', $providerId)
                ->where('type', 'tip_credit')
                ->sum('amount');

            $todayEarnings = $todayBookingEarnings + $todayTipEarnings;
            $weekEarnings = $weekBookingEarnings + $weekTipEarnings;
            $monthEarnings = $monthBookingEarnings + $monthTipEarnings;
            $totalEarnings = $totalBookingEarnings + $totalTipEarnings;

            $recentTransactions = Booking::where('provider_id', $providerId)
                ->where('status', 'completed')
                ->with(['service', 'taker:id,name', 'payments:id,booking_id,method,captured_at,created_at'])
                ->latest('updated_at')
                ->take(20)
                ->get();

            return compact('todayEarnings', 'weekEarnings', 'monthEarnings', 'totalEarnings', 'recentTransactions');
        });

        return view('pages.provider.earnings', $payload);
    }

    public function reviews(): View
    {
        $providerId = Auth::id();
        $cacheKey = 'provider.reviews:' . $providerId . ':' . request()->get('page', 1);

        $payload = Cache::remember($cacheKey, now()->addMinutes(2), function () use ($providerId) {
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

            return compact('reviews', 'avgRating', 'totalReviews', 'ratingDistribution');
        });

        return view('pages.provider.reviews', $payload);
    }

    public function schedule(): View
    {
        $providerId = Auth::id();
        $payload = Cache::remember('provider.schedule:' . $providerId, now()->addMinutes(2), function () use ($providerId) {
            $upcomingBookings = Booking::where('provider_id', $providerId)
                ->whereIn('status', ['pending', 'active', 'in_progress', 'awaiting_confirmation'])
                ->with(['service', 'taker:id,first_name,last_name,name,phone'])
                ->orderBy('scheduled_at')
                ->get();

            return compact('upcomingBookings');
        });

        return view('pages.provider.schedule', $payload);
    }

    public function analytics(): View
    {
        $providerId = Auth::id();
        $now = Carbon::now();

        $payload = Cache::remember('provider.analytics:' . $providerId . ':' . $now->format('Y-m'), now()->addMinutes(10), function () use ($providerId, $now) {
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

            return compact(
                'monthlyEarnings', 'totalBookings', 'completedBookings', 'cancelledBookings',
                'avgRating', 'totalReviews', 'uniqueClients'
            );
        });

        return view('pages.provider.analytics', $payload);
    }

    public function settings(): View
    {
        $user = Auth::user();
        return view('pages.provider.settings', compact('user'));
    }
}

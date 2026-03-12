<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Review;

class ProviderDashboardController extends Controller
{
    public function index()
    {
        $providerId = auth()->id();
        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $weekStart = $now->copy()->subDays(7);

        $stats = [
            'today_earnings' => 0.0,
            'jobs_completed' => 0,
            'avg_rating' => null,
            'active_requests' => 0,
        ];

        $performance = [
            'response_rate' => null,
            'completion_rate' => null,
            'on_time_arrival' => null,
        ];

        // Calculate real performance metrics
        $totalBookings = Booking::where('provider_id', $providerId)->count();

        if ($totalBookings > 0) {
            // Response rate: bookings that were not left in pending (i.e. accepted or acted on)
            $respondedBookings = Booking::where('provider_id', $providerId)
                ->whereIn('status', ['active', 'in_progress', 'completed', 'cancelled'])
                ->count();
            $performance['response_rate'] = round(($respondedBookings / $totalBookings) * 100);

            // Completion rate: completed out of total non-pending
            $nonPending = Booking::where('provider_id', $providerId)
                ->where('status', '!=', 'pending')
                ->count();
            if ($nonPending > 0) {
                $completedCount = Booking::where('provider_id', $providerId)->where('status', 'completed')->count();
                $performance['completion_rate'] = round(($completedCount / $nonPending) * 100);
            }

            // On-time arrival: bookings completed where updated_at <= scheduled_at + 1 hour
            $scheduledCompleted = Booking::where('provider_id', $providerId)
                ->where('status', 'completed')
                ->whereNotNull('scheduled_at')
                ->count();
            if ($scheduledCompleted > 0) {
                $onTime = Booking::where('provider_id', $providerId)
                    ->where('status', 'completed')
                    ->whereNotNull('scheduled_at')
                    ->whereColumn('updated_at', '<=', \DB::raw("DATE_ADD(scheduled_at, INTERVAL 1 HOUR)"))
                    ->count();
                $performance['on_time_arrival'] = round(($onTime / $scheduledCompleted) * 100);
            }
        }

        $quickStats = [
            'week_earnings' => 0.0,
            'clients_count' => 0,
        ];

        $recentBookings = Booking::where('provider_id', $providerId)
            ->with('service')
            ->latest()
            ->take(5)
            ->get();

        $recentJobs = $recentBookings->map(function (Booking $booking) {
            $statusMap = [
                'completed' => ['badge-success', 'Completed'],
                'pending' => ['badge-warning', 'Pending'],
                'active' => ['badge-info', 'Active'],
                'in_progress' => ['badge-info', 'In Progress'],
                'cancelled' => ['badge-error', 'Cancelled'],
            ];

            $status = $booking->status ?? 'pending';
            [$badgeClass, $label] = $statusMap[$status] ?? ['badge-ghost', ucfirst($status)];

            return [
                'title' => $booking->service?->name ?? 'Service',
                'description' => $booking->notes ?? '',
                'time' => $booking->created_at?->diffForHumans() ?? '',
                'badge_class' => $badgeClass,
                'status_label' => $label,
            ];
        })->toArray();

        $stats['jobs_completed'] = Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->count();

        $stats['active_requests'] = Booking::where('provider_id', $providerId)
            ->whereIn('status', ['pending', 'active', 'in_progress'])
            ->count();

        $stats['today_earnings'] = (float) Booking::where('provider_id', $providerId)
            ->where('created_at', '>=', $todayStart)
            ->sum('total');

        $quickStats['week_earnings'] = (float) Booking::where('provider_id', $providerId)
            ->where('created_at', '>=', $weekStart)
            ->sum('total');

        $quickStats['clients_count'] = Booking::where('provider_id', $providerId)
            ->distinct('taker_id')
            ->count('taker_id');

        $reviewRows = Review::where('provider_id', $providerId)
            ->with('taker')
            ->latest()
            ->take(6)
            ->get();

        $reviews = $reviewRows->map(function (Review $review) {
            return [
                'author' => $review->taker?->name ?? 'Anonymous',
                'text' => $review->comment ?? '',
                'rating' => $review->rating,
                'time' => $review->created_at?->diffForHumans() ?? '',
            ];
        })->toArray();

        $stats['avg_rating'] = Review::where('provider_id', $providerId)->avg('rating');
        if ($stats['avg_rating'] !== null) {
            $stats['avg_rating'] = round((float) $stats['avg_rating'], 2);
        }

        return view('pages.provider_dashboard', [
            'stats' => $stats,
            'performance' => $performance,
            'quickStats' => $quickStats,
            'recentJobs' => $recentJobs,
            'reviews' => $reviews,
        ]);
    }
}

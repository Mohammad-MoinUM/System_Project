<?php

namespace App\Http\Controllers;

use App\Models\BookingChatMessage;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\ProviderPayoutRequest;
use App\Models\Review;
use App\Models\SupportConversation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProviderDashboardController extends Controller
{
    public function index()
    {
        $providerId = Auth::id();
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
                $onTimeQuery = Booking::where('provider_id', $providerId)
                    ->where('status', 'completed')
                    ->whereNotNull('scheduled_at');

                $driver = DB::getDriverName();
                if (in_array($driver, ['mysql', 'mariadb'], true)) {
                    $onTimeQuery->whereRaw("updated_at <= DATE_ADD(scheduled_at, INTERVAL 1 HOUR)");
                } elseif ($driver === 'pgsql') {
                    $onTimeQuery->whereRaw("updated_at <= scheduled_at + interval '1 hour'");
                } else {
                    $onTimeQuery->whereRaw("updated_at <= datetime(scheduled_at, '+1 hour')");
                }

                $onTime = $onTimeQuery->count();
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

        $monthlyEarningsTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $amount = (float) Booking::where('provider_id', $providerId)
                ->where('status', 'completed')
                ->whereYear('updated_at', $month->year)
                ->whereMonth('updated_at', $month->month)
                ->sum('total');

            $monthlyEarningsTrend->push([
                'label' => $month->format('M'),
                'amount' => $amount,
            ]);
        }

        $currentMonthEarnings = (float) Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('total');

        $lastMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth();

        $lastMonthEarnings = (float) Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('total');

        $earningsDeltaPercent = null;
        if ($lastMonthEarnings > 0) {
            $earningsDeltaPercent = round((($currentMonthEarnings - $lastMonthEarnings) / $lastMonthEarnings) * 100);
        }

        $servicePerformance = Booking::where('bookings.provider_id', $providerId)
            ->where('bookings.status', 'completed')
            ->join('services', 'services.id', '=', 'bookings.service_id')
            ->select('services.name', 'services.category', DB::raw('COUNT(bookings.id) as completed_count'), DB::raw('SUM(bookings.total) as revenue'))
            ->groupBy('services.name', 'services.category')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $monthCompletedJobs = Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->count();

        $growthMissions = collect([
            [
                'title' => 'Complete 20 jobs this month',
                'current' => $monthCompletedJobs,
                'target' => 20,
                'reward' => 'Featured provider boost',
            ],
            [
                'title' => 'Keep response rate at 90%+',
                'current' => (int) ($performance['response_rate'] ?? 0),
                'target' => 90,
                'reward' => 'Priority listing badge',
            ],
            [
                'title' => 'Maintain 4.5+ average rating',
                'current' => (int) round(((float) ($stats['avg_rating'] ?? 0)) * 20),
                'target' => 90,
                'reward' => 'Trust badge upgrade',
            ],
        ])->map(function ($mission) {
            $mission['percent'] = (int) min(100, round(($mission['current'] / max($mission['target'], 1)) * 100));
            return $mission;
        });

        $growthTips = collect();
        if (($performance['response_rate'] ?? 100) < 80) {
            $growthTips->push([
                'title' => 'Improve response speed',
                'description' => 'Reply faster to new requests to improve visibility.',
                'badge' => 'Important',
            ]);
        }

        if (($performance['completion_rate'] ?? 100) < 85) {
            $growthTips->push([
                'title' => 'Increase completion rate',
                'description' => 'Avoid cancellations to maintain customer trust.',
                'badge' => 'Quality',
            ]);
        }

        if (($stats['avg_rating'] ?? 0) < 4.5) {
            $growthTips->push([
                'title' => 'Boost your ratings',
                'description' => 'Follow up after completion and request customer reviews.',
                'badge' => 'Reputation',
            ]);
        }

        if ($growthTips->isEmpty()) {
            $growthTips->push([
                'title' => 'Great momentum',
                'description' => 'You are performing strongly. Keep consistency for top rank.',
                'badge' => 'Top',
            ]);
        }

        $unreadSupportReplies = 0;
        $supportConversation = SupportConversation::where('user_id', $providerId)->first();
        if ($supportConversation) {
            $unreadSupportReplies = $supportConversation->messages()
                ->where('is_read', false)
                ->where('sender_id', '!=', $providerId)
                ->count();
        }

        $unreadBookingChats = 0;
        if (Schema::hasTable('booking_chat_messages')) {
            $providerBookingIds = Booking::where('provider_id', $providerId)->pluck('id');
            if ($providerBookingIds->isNotEmpty()) {
                $unreadBookingChats = BookingChatMessage::whereIn('booking_id', $providerBookingIds)
                    ->where('sender_id', '!=', $providerId)
                    ->where('is_read', false)
                    ->count();
            }
        }

        $pendingPayoutCount = 0;
        $pendingPayoutAmount = 0.0;
        if (Schema::hasTable('provider_payout_requests')) {
            $pendingPayoutCount = ProviderPayoutRequest::where('user_id', $providerId)
                ->where('status', 'pending')
                ->count();
            $pendingPayoutAmount = (float) ProviderPayoutRequest::where('user_id', $providerId)
                ->where('status', 'pending')
                ->sum('amount');
        }

        $serviceAreaCount = 0;
        if (Schema::hasTable('provider_service_areas')) {
            $serviceAreaCount = DB::table('provider_service_areas')
                ->where('user_id', $providerId)
                ->where('is_active', true)
                ->count();
        }

        return view('pages.provider_dashboard', [
            'stats' => $stats,
            'performance' => $performance,
            'quickStats' => $quickStats,
            'recentJobs' => $recentJobs,
            'reviews' => $reviews,
            'monthlyEarningsTrend' => $monthlyEarningsTrend,
            'currentMonthEarnings' => $currentMonthEarnings,
            'lastMonthEarnings' => $lastMonthEarnings,
            'earningsDeltaPercent' => $earningsDeltaPercent,
            'servicePerformance' => $servicePerformance,
            'growthMissions' => $growthMissions,
            'growthTips' => $growthTips,
            'unreadSupportReplies' => $unreadSupportReplies,
            'unreadBookingChats' => $unreadBookingChats,
            'pendingPayoutCount' => $pendingPayoutCount,
            'pendingPayoutAmount' => $pendingPayoutAmount,
            'serviceAreaCount' => $serviceAreaCount,
        ]);
    }

    public function downloadMonthlyInvoice(Request $request)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
        ]);

        $month = (int) ($validated['month'] ?? now()->month);
        $year = (int) ($validated['year'] ?? now()->year);

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $providerId = Auth::id();

        $bookings = Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$start, $end])
            ->with(['service:id,name,category', 'taker:id,name,email'])
            ->orderBy('updated_at')
            ->get();

        $totalAmount = (float) $bookings->sum('total');
        $totalJobs = $bookings->count();

        $pdf = Pdf::loadView('receipts.provider_monthly_statement', [
            'provider' => Auth::user(),
            'monthLabel' => $start->format('F Y'),
            'bookings' => $bookings,
            'totalAmount' => $totalAmount,
            'totalJobs' => $totalJobs,
            'generatedAt' => now(),
        ])->setPaper('a4');

        return $pdf->download('provider-invoice-' . $start->format('Y-m') . '.pdf');
    }
}

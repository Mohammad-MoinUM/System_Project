<?php

namespace App\Http\Controllers;

use App\Models\BookingChatMessage;
use App\Services\ProviderDashboardFeatureService;
use Carbon\Carbon;
use App\Models\Booking;
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

        // Earnings should reflect jobs completed today, not newly created bookings.
        $stats['today_earnings'] = (float) Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->where('updated_at', '>=', $todayStart)
            ->sum('total');

        $quickStats['week_earnings'] = (float) Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->where('updated_at', '>=', $weekStart)
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

        // New feature: earnings forecast based on current month run rate.
        $daysElapsedInMonth = max(1, $now->copy()->startOfDay()->diffInDays($now->copy()->startOfMonth()) + 1);
        $daysInMonth = (int) $now->daysInMonth;
        $dailyRunRate = $currentMonthEarnings / $daysElapsedInMonth;
        $forecastMonthEarnings = round($dailyRunRate * $daysInMonth, 2);

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

        // New feature: trust level and progress milestones.
        $ratingScore = (int) round(min(100, max(0, (((float) ($stats['avg_rating'] ?? 0)) / 5) * 100)));
        $responseScore = (int) round(min(100, max(0, (float) ($performance['response_rate'] ?? 0))));
        $completionScore = (int) round(min(100, max(0, (float) ($performance['completion_rate'] ?? 0))));
        $onTimeScore = (int) round(min(100, max(0, (float) ($performance['on_time_arrival'] ?? 0))));
        $volumeScore = (int) round(min(100, max(0, $stats['jobs_completed'] ?? 0)));

        $trustScore = (int) round(
            ($ratingScore * 0.30) +
            ($responseScore * 0.20) +
            ($completionScore * 0.20) +
            ($onTimeScore * 0.20) +
            ($volumeScore * 0.10)
        );

        $trustLevel = match (true) {
            $trustScore >= 85 => 'Elite Provider',
            $trustScore >= 70 => 'Trusted Provider',
            $trustScore >= 50 => 'Rising Provider',
            default => 'Starter Provider',
        };

        $trustMilestones = collect([
            [
                'title' => 'Complete 25 total jobs',
                'current' => (int) ($stats['jobs_completed'] ?? 0),
                'target' => 25,
            ],
            [
                'title' => 'Keep average rating at 4.7+',
                'current' => (int) round(((float) ($stats['avg_rating'] ?? 0)) * 10),
                'target' => 47,
            ],
            [
                'title' => 'Maintain on-time arrival at 90%+',
                'current' => (int) ($performance['on_time_arrival'] ?? 0),
                'target' => 90,
            ],
        ])->map(function (array $milestone) {
            $target = max(1, (int) $milestone['target']);
            $current = max(0, (int) $milestone['current']);
            $milestone['percent'] = (int) min(100, round(($current / $target) * 100));
            $milestone['remaining'] = max(0, $target - $current);
            return $milestone;
        })->values();

        // New feature: booking conversion funnel.
        $bookingFunnel = [
            'pending' => Booking::where('provider_id', $providerId)->where('status', 'pending')->count(),
            'active' => Booking::where('provider_id', $providerId)->where('status', 'active')->count(),
            'in_progress' => Booking::where('provider_id', $providerId)->where('status', 'in_progress')->count(),
            'completed' => Booking::where('provider_id', $providerId)->where('status', 'completed')->count(),
            'cancelled' => Booking::where('provider_id', $providerId)->where('status', 'cancelled')->count(),
        ];

        $totalLifecycleBookings = array_sum($bookingFunnel);
        $acceptedBookings = $bookingFunnel['active'] + $bookingFunnel['in_progress'] + $bookingFunnel['completed'];
        $bookingFunnelRates = [
            'acceptance' => $totalLifecycleBookings > 0
                ? (int) round(($acceptedBookings / $totalLifecycleBookings) * 100)
                : null,
            'completion' => $acceptedBookings > 0
                ? (int) round(($bookingFunnel['completed'] / $acceptedBookings) * 100)
                : null,
            'cancellation' => $totalLifecycleBookings > 0
                ? (int) round(($bookingFunnel['cancelled'] / $totalLifecycleBookings) * 100)
                : null,
        ];

        // New feature: repeat client radar.
        $repeatClientRows = Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->select(
                'taker_id',
                DB::raw('COUNT(*) as completed_jobs'),
                DB::raw('MAX(updated_at) as last_completed_at'),
                DB::raw('AVG(total) as average_order_value')
            )
            ->groupBy('taker_id')
            ->havingRaw('COUNT(*) >= 2')
            ->orderByDesc(DB::raw('MAX(updated_at)'))
            ->limit(5)
            ->get();

        $repeatClientNames = DB::table('users')
            ->whereIn('id', $repeatClientRows->pluck('taker_id'))
            ->pluck('name', 'id');

        $repeatClientRadar = $repeatClientRows->map(function ($row) use ($repeatClientNames) {
            return [
                'name' => $repeatClientNames[(int) $row->taker_id] ?? 'Customer',
                'completed_jobs' => (int) $row->completed_jobs,
                'last_completed_human' => Carbon::parse($row->last_completed_at)->diffForHumans(),
                'avg_order_value' => (float) $row->average_order_value,
            ];
        })->values();

        $forecastConfidence = (int) min(95, max(35, round(35 + ($monthCompletedJobs * 2.5))));

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

        // Pending payouts are completed cash jobs without a captured cash payment record.
        $pendingCashBookings = Booking::query()
            ->where('provider_id', $providerId)
            ->where('status', 'completed')
            ->where('payment_method', 'cash')
            ->whereDoesntHave('payments', function ($query) {
                $query->where('type', 'cash_on_service')
                    ->where('status', 'captured');
            });

        $pendingPayoutCount = (int) (clone $pendingCashBookings)->count();
        $pendingPayoutAmount = (float) (clone $pendingCashBookings)->sum('total');

        $serviceAreaCount = 0;
        if (Schema::hasTable('provider_service_areas')) {
            $serviceAreaCount = DB::table('provider_service_areas')
                ->where('user_id', $providerId)
                ->where('is_active', true)
                ->count();
        }

        $featureLabData = app(ProviderDashboardFeatureService::class)->build($providerId, $now);

        $smartDemandInsights = $this->buildSmartDemandInsights($providerId, $now);

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
            'dailyRunRate' => $dailyRunRate,
            'forecastMonthEarnings' => $forecastMonthEarnings,
            'forecastConfidence' => $forecastConfidence,
            'servicePerformance' => $servicePerformance,
            'trustScore' => $trustScore,
            'trustLevel' => $trustLevel,
            'trustMilestones' => $trustMilestones,
            'bookingFunnel' => $bookingFunnel,
            'bookingFunnelRates' => $bookingFunnelRates,
            'repeatClientRadar' => $repeatClientRadar,
            'growthMissions' => $growthMissions,
            'growthTips' => $growthTips,
            'unreadSupportReplies' => $unreadSupportReplies,
            'unreadBookingChats' => $unreadBookingChats,
            'pendingPayoutCount' => $pendingPayoutCount,
            'pendingPayoutAmount' => $pendingPayoutAmount,
            'serviceAreaCount' => $serviceAreaCount,
            'retentionOpportunities' => $featureLabData['retentionOpportunities'],
            'pricingInsights' => $featureLabData['pricingInsights'],
            'nextBestActions' => $featureLabData['nextBestActions'],
            'smartDemandInsights' => $smartDemandInsights,
        ]);
    }

    private function buildSmartDemandInsights(int $providerId, Carbon $now): array
    {
        $completedBookings = Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->whereNotNull('scheduled_at')
            ->latest('scheduled_at')
            ->take(1000)
            ->get(['scheduled_at', 'total']);

        $hourBuckets = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourBuckets[$hour] = 0;
        }

        $weekdayBuckets = [];
        for ($weekday = 0; $weekday < 7; $weekday++) {
            $weekdayBuckets[$weekday] = 0;
        }

        $averageTicket = 0.0;
        if ($completedBookings->isNotEmpty()) {
            $averageTicket = (float) $completedBookings->avg('total');
        }

        foreach ($completedBookings as $booking) {
            if (!$booking->scheduled_at) {
                continue;
            }

            $timestamp = Carbon::parse($booking->scheduled_at);
            $hourBuckets[(int) $timestamp->hour]++;
            $weekdayBuckets[(int) $timestamp->dayOfWeek]++;
        }

        arsort($hourBuckets);
        arsort($weekdayBuckets);

        $topHours = array_slice($hourBuckets, 0, 3, true);
        $topWeekdays = array_slice($weekdayBuckets, 0, 3, true);

        $weekdayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $maxHourCount = max(1, ...array_values($hourBuckets));
        $maxWeekdayCount = max(1, ...array_values($weekdayBuckets));

        $topHourCards = collect($topHours)->map(function ($count, $hour) use ($maxHourCount) {
            $start = str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':00';
            $endHour = ((int) $hour + 1) % 24;
            $end = str_pad((string) $endHour, 2, '0', STR_PAD_LEFT) . ':00';

            return [
                'label' => $start . ' - ' . $end,
                'count' => (int) $count,
                'score' => (int) round(((int) $count / $maxHourCount) * 100),
                'hour' => (int) $hour,
            ];
        })->values();

        $topDayCards = collect($topWeekdays)->map(function ($count, $weekday) use ($maxWeekdayCount, $weekdayLabels) {
            return [
                'label' => $weekdayLabels[(int) $weekday] ?? 'N/A',
                'count' => (int) $count,
                'score' => (int) round(((int) $count / $maxWeekdayCount) * 100),
                'weekday' => (int) $weekday,
            ];
        })->values();

        $preferredWeekdays = $topDayCards->pluck('weekday')->take(2)->all();
        $preferredHours = $topHourCards->pluck('hour')->take(2)->all();

        $suggestedSlots = collect();
        for ($i = 0; $i < 14; $i++) {
            $day = $now->copy()->startOfDay()->addDays($i + 1);
            if (!in_array($day->dayOfWeek, $preferredWeekdays, true)) {
                continue;
            }

            foreach ($preferredHours as $hour) {
                $slot = $day->copy()->setHour((int) $hour)->setMinute(0)->setSecond(0);
                if ($slot->lessThanOrEqualTo($now)) {
                    continue;
                }

                $hourVolume = (int) ($hourBuckets[(int) $hour] ?? 0);
                $confidence = (int) round(($hourVolume / $maxHourCount) * 100);
                $projectedEarning = round($averageTicket * (0.85 + ($confidence / 200)), 2);

                $suggestedSlots->push([
                    'day_label' => $slot->format('D, d M'),
                    'time_label' => $slot->format('g:i A'),
                    'confidence' => max(35, $confidence),
                    'projected_earning' => $projectedEarning,
                ]);

                if ($suggestedSlots->count() >= 6) {
                    break 2;
                }
            }
        }

        return [
            'has_history' => $completedBookings->isNotEmpty(),
            'total_analyzed' => $completedBookings->count(),
            'average_ticket' => $averageTicket,
            'top_hours' => $topHourCards,
            'top_days' => $topDayCards,
            'suggested_slots' => $suggestedSlots,
        ];
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

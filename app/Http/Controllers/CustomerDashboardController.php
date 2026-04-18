<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Review;
use App\Models\SavedProvider;
use App\Models\SupportConversation;
use App\Models\Wallet;
use App\Models\User;
use App\Services\UnsplashService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    public function __construct(protected UnsplashService $unsplash) {}

    public function index(): View
    {
        $user = Auth::user();
        $now = now();

        // Active bookings
        $activeBookings = Booking::where('taker_id', $user->id)
            ->where('status', 'active')
            ->with(['service', 'provider:id,name'])
            ->get();

        // Recent completed bookings
        $recentHistory = Booking::where('taker_id', $user->id)
            ->where('status', 'completed')
            ->with('service')
            ->latest()
            ->take(5)
            ->get();

        $completedBookingsQuery = Booking::where('taker_id', $user->id)
            ->where('status', 'completed')
            ->with('service:id,name,category');

        // Stats
        $totalSpent = (float) (clone $completedBookingsQuery)->sum('total');

        $servicesUsed = (clone $completedBookingsQuery)
            ->count();

        $savedProviders = SavedProvider::where('taker_id', $user->id)->count();

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'cashback_balance' => 0]
        );

        $unreadNotificationsCount = $user->unreadNotifications()->count();

        // Provider recommendations based on the customer's booking categories
        $preferredCategories = Booking::where('taker_id', $user->id)
            ->join('services', 'bookings.service_id', '=', 'services.id')
            ->whereNotNull('services.category')
            ->pluck('services.category')
            ->filter()
            ->unique()
            ->values();

        $recommendedProvidersQuery = User::query()
            ->where('role', 'provider')
            ->whereHas('servicesProvided', function ($query) {
                $query->where('is_active', true);
            })
            ->withCount([
                'bookingsAsProvider as completed_jobs_count' => function ($query) {
                    $query->where('status', 'completed');
                },
                'servicesProvided as active_services_count' => function ($query) {
                    $query->where('is_active', true);
                },
            ])
            ->withCount([
                'bookingsAsProvider as total_jobs_count',
                'bookingsAsProvider as cancelled_jobs_count' => function ($query) {
                    $query->where('status', 'cancelled');
                },
            ])
            ->withAvg('reviewsReceived as avg_rating', 'rating')
            ->with([
                'servicesProvided' => function ($query) {
                    $query->where('is_active', true)
                        ->select('id', 'provider_id', 'name', 'category');
                },
            ]);

        // Some environments may not have this column yet.
        if (Schema::hasColumn('users', 'verification_status')) {
            $recommendedProvidersQuery->where('verification_status', 'approved');
        }

        if ($preferredCategories->isNotEmpty()) {
            $recommendedProvidersQuery->whereHas('servicesProvided', function ($query) use ($preferredCategories) {
                $query->where('is_active', true)
                    ->whereIn('category', $preferredCategories->all());
            });
        }

        $recommendedProviders = $recommendedProvidersQuery
            ->orderByDesc('completed_jobs_count')
            ->orderByDesc('active_services_count')
            ->limit(3)
            ->get()
            ->map(function (User $provider) {
                $totalJobs = max((int) ($provider->total_jobs_count ?? 0), 1);
                $completionRate = round(((int) ($provider->completed_jobs_count ?? 0) / $totalJobs) * 100);
                $rating = round((float) ($provider->avg_rating ?? 0), 2);

                if (($provider->completed_jobs_count ?? 0) >= 100 && $rating >= 4.7) {
                    $provider->trust_badge = 'Elite';
                } elseif ($completionRate >= 90 && $rating >= 4.5) {
                    $provider->trust_badge = 'Reliable';
                } elseif (($provider->completed_jobs_count ?? 0) >= 20) {
                    $provider->trust_badge = 'Proven';
                } else {
                    $provider->trust_badge = 'Rising';
                }

                $provider->completion_rate = $completionRate;

                return $provider;
            });

        // Unread admin replies in support conversation
        $supportConversation = SupportConversation::where('user_id', $user->id)->first();
        $unreadSupportReplies = 0;
        if ($supportConversation) {
            $unreadSupportReplies = $supportConversation->messages()
                ->where('is_read', false)
                ->where('sender_id', '!=', $user->id)
                ->count();
        }

        // Popular services ranked by avg rating, review count, and bookings
        $serviceIds = DB::table('services')
            ->where('services.is_active', true)
            ->leftJoin('bookings', 'bookings.service_id', '=', 'services.id')
            ->leftJoin('reviews', 'reviews.booking_id', '=', 'bookings.id')
            ->select(
                'services.id',
                DB::raw('COUNT(DISTINCT bookings.id) as bookings_count'),
                DB::raw('COUNT(DISTINCT reviews.id) as reviews_count'),
                DB::raw('COALESCE(AVG(reviews.rating), 0) as avg_rating')
            )
            ->groupBy('services.id')
            ->orderByDesc('avg_rating')
            ->orderByDesc('reviews_count')
            ->orderByDesc('bookings_count')
            ->limit(6)
            ->pluck('services.id');

        $popularServices = Service::whereIn('id', $serviceIds)
            ->get()
            ->sortBy(fn($s) => $serviceIds->search($s->id))
            ->values();

        // Attach stats
        $statsMap = DB::table('services')
            ->whereIn('services.id', $serviceIds)
            ->leftJoin('bookings', 'bookings.service_id', '=', 'services.id')
            ->leftJoin('reviews', 'reviews.booking_id', '=', 'bookings.id')
            ->select(
                'services.id',
                DB::raw('COUNT(DISTINCT bookings.id) as bookings_count'),
                DB::raw('COUNT(DISTINCT reviews.id) as reviews_count'),
                DB::raw('COALESCE(AVG(reviews.rating), 0) as avg_rating')
            )
            ->groupBy('services.id')
            ->get()
            ->keyBy('id');

        foreach ($popularServices as $service) {
            $stats = $statsMap[$service->id] ?? null;
            $service->bookings_count = $stats->bookings_count ?? 0;
            $service->reviews_count = $stats->reviews_count ?? 0;
            $service->avg_rating = $stats->avg_rating ?? 0;
        }

        // Fetch Unsplash images for popular service names
        $serviceNames = $popularServices->pluck('name')->filter()->unique()->values()->all();
        $serviceImages = Cache::remember(
            'popular_service_images_' . md5(json_encode($serviceNames)),
            now()->addDay(),
            fn() => $this->unsplash->getImagesForCategories($serviceNames, 'small')
        );

        // Reviews
        $reviews = Review::where('taker_id', $user->id)
            ->with(['provider', 'booking.service'])
            ->latest()
            ->get();

        $topServiceUsage = Booking::where('taker_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('service_id')
            ->select('service_id', DB::raw('COUNT(*) as usage_count'), DB::raw('MAX(updated_at) as last_booked_at'))
            ->groupBy('service_id')
            ->orderByDesc('usage_count')
            ->limit(3)
            ->get();

        $topServices = Service::whereIn('id', $topServiceUsage->pluck('service_id')->all())
            ->get()
            ->keyBy('id');

        $serviceCycleDays = [
            'Cleaning' => 14,
            'Pest Control' => 90,
            'AC Service' => 120,
            'Plumbing' => 30,
            'Electrical' => 45,
        ];

        $smartRebookSuggestions = $topServiceUsage->map(function ($row) use ($topServices, $serviceCycleDays) {
            $service = $topServices->get((int) $row->service_id);
            if (!$service) {
                return null;
            }

            $cycle = $serviceCycleDays[$service->category ?? ''] ?? 30;
            $suggestedDate = Carbon::parse($row->last_booked_at)->addDays($cycle);

            return [
                'service' => $service,
                'usage_count' => (int) $row->usage_count,
                'last_booked_at' => Carbon::parse($row->last_booked_at),
                'suggested_date' => $suggestedDate,
                'cycle_days' => $cycle,
            ];
        })->filter()->values();

        $monthlySpendTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $amount = (float) Booking::where('taker_id', $user->id)
                ->where('status', 'completed')
                ->whereYear('updated_at', $month->year)
                ->whereMonth('updated_at', $month->month)
                ->sum('total');

            $monthlySpendTrend->push([
                'label' => $month->format('M'),
                'amount' => $amount,
            ]);
        }

        $currentMonthSpend = (float) Booking::where('taker_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('total');

        $lastMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth();
        $lastMonthSpend = (float) Booking::where('taker_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('total');

        $spendDeltaPercent = null;
        if ($lastMonthSpend > 0) {
            $spendDeltaPercent = round((($currentMonthSpend - $lastMonthSpend) / $lastMonthSpend) * 100);
        }

        $categorySpend = Booking::where('bookings.taker_id', $user->id)
            ->where('bookings.status', 'completed')
            ->join('services', 'services.id', '=', 'bookings.service_id')
            ->select('services.category', DB::raw('SUM(bookings.total) as total'))
            ->groupBy('services.category')
            ->orderByDesc('total')
            ->limit(4)
            ->get();

        $bookingsThisMonth = Booking::where('taker_id', $user->id)
            ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->count();

        $reviewsThisMonth = Review::where('taker_id', $user->id)
            ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->count();

        $newCategoryCountThisMonth = Booking::where('bookings.taker_id', $user->id)
            ->whereBetween('bookings.created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->join('services', 'services.id', '=', 'bookings.service_id')
            ->distinct('services.category')
            ->count('services.category');

        $loyaltyMissions = collect([
            [
                'title' => 'Book 3 services this month',
                'current' => $bookingsThisMonth,
                'target' => 3,
                'reward' => '100 points',
            ],
            [
                'title' => 'Submit 2 reviews this month',
                'current' => $reviewsThisMonth,
                'target' => 2,
                'reward' => '50 points',
            ],
            [
                'title' => 'Try 2 different categories',
                'current' => $newCategoryCountThisMonth,
                'target' => 2,
                'reward' => 'Cashback boost 5%',
            ],
        ])->map(function ($mission) {
            $mission['percent'] = (int) min(100, round(($mission['current'] / max($mission['target'], 1)) * 100));
            return $mission;
        });

        $lastCompletedBookingDate = Booking::where('taker_id', $user->id)
            ->where('status', 'completed')
            ->latest('updated_at')
            ->value('updated_at');

        $dynamicOffers = collect();
        if ($servicesUsed === 0) {
            $dynamicOffers->push([
                'title' => 'Welcome Offer',
                'description' => 'Get 15% off your first booking this week.',
                'badge' => 'New',
            ]);
        }

        if ($lastCompletedBookingDate && Carbon::parse($lastCompletedBookingDate)->lt($now->copy()->subDays(30))) {
            $dynamicOffers->push([
                'title' => 'Come Back Discount',
                'description' => 'Flat 10% off if you book in the next 72 hours.',
                'badge' => 'Win-back',
            ]);
        }

        if ($currentMonthSpend > 0 && $lastMonthSpend > 0 && $currentMonthSpend > ($lastMonthSpend * 1.2)) {
            $dynamicOffers->push([
                'title' => 'Cashback Booster',
                'description' => 'Pay via wallet this week and get 8% cashback.',
                'badge' => 'Smart Save',
            ]);
        }

        if ((float) $wallet->balance < 200) {
            $dynamicOffers->push([
                'title' => 'Wallet Top-up Perk',
                'description' => 'Top up BDT 500 and get bonus credits instantly.',
                'badge' => 'Wallet',
            ]);
        }

        $serviceRemindersRaw = Booking::where('bookings.taker_id', $user->id)
            ->where('bookings.status', 'completed')
            ->join('services', 'services.id', '=', 'bookings.service_id')
            ->select('services.category', DB::raw('MAX(bookings.updated_at) as last_done_at'))
            ->groupBy('services.category')
            ->get();

        $reminderCycleDays = [
            'Cleaning' => 14,
            'Pest Control' => 90,
            'AC Service' => 120,
            'Plumbing' => 30,
            'Electrical' => 45,
        ];

        $serviceReminders = $serviceRemindersRaw->map(function ($row) use ($reminderCycleDays, $now) {
            $category = $row->category ?: 'General Service';
            $cycle = $reminderCycleDays[$category] ?? 30;
            $dueDate = Carbon::parse($row->last_done_at)->addDays($cycle);

            return [
                'category' => $category,
                'last_done_at' => Carbon::parse($row->last_done_at),
                'due_date' => $dueDate,
                'is_due' => $dueDate->lte($now),
            ];
        })->sortBy('due_date')->take(4)->values();

        $successfulReferrals = $user->referrals()->whereNotNull('email_verified_at')->count();
        $pendingReferrals = $user->referrals()->whereNull('email_verified_at')->count();
        $estimatedReferralRewards = $successfulReferrals * 50;

        return view('pages.customer_dashboard', compact(
            'activeBookings',
            'recentHistory',
            'totalSpent',
            'servicesUsed',
            'savedProviders',
            'wallet',
            'recommendedProviders',
            'unreadSupportReplies',
            'unreadNotificationsCount',
            'popularServices',
            'serviceImages',
            'reviews',
            'smartRebookSuggestions',
            'monthlySpendTrend',
            'currentMonthSpend',
            'lastMonthSpend',
            'spendDeltaPercent',
            'categorySpend',
            'loyaltyMissions',
            'dynamicOffers',
            'serviceReminders',
            'successfulReferrals',
            'pendingReferrals',
            'estimatedReferralRewards'
        ));
    }

    public function history(): View
    {
        $user = Auth::user();

        $bookings = Booking::where('taker_id', $user->id)
            ->with(['service', 'provider:id,first_name,last_name,name,phone', 'reviews'])
            ->latest()
            ->paginate(15);

        return view('pages.customer_history', compact('bookings'));
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

        $user = Auth::user();

        $bookings = Booking::where('taker_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$start, $end])
            ->with(['service:id,name,category', 'provider:id,name'])
            ->orderBy('updated_at')
            ->get();

        $totalAmount = (float) $bookings->sum('total');
        $totalJobs = $bookings->count();

        $pdf = Pdf::loadView('receipts.customer_monthly_statement', [
            'user' => $user,
            'monthLabel' => $start->format('F Y'),
            'bookings' => $bookings,
            'totalAmount' => $totalAmount,
            'totalJobs' => $totalJobs,
            'generatedAt' => now(),
        ])->setPaper('a4');

        return $pdf->download('customer-invoice-' . $start->format('Y-m') . '.pdf');
    }
}
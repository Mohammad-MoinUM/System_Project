<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Review;
use App\Models\SavedProvider;
use App\Services\UnsplashService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    public function __construct(protected UnsplashService $unsplash) {}
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

        return view('pages.customer_dashboard', compact(
            'activeBookings',
            'recentHistory',
            'totalSpent',
            'servicesUsed',
            'savedProviders',
            'popularServices',
            'serviceImages',
            'reviews'
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
}
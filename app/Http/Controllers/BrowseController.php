<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Review;
use App\Services\UnsplashService;
use App\Services\SlotGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BrowseController extends Controller
{
    public function __construct(protected UnsplashService $unsplash) {}

    /**
     * Display all service categories with images
     */
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim();

        // Fetch categories with counts
        $categories = Service::query()
            ->where('is_active', true)
            ->when($search, fn($query) => $query->where('category', 'like', "%{$search}%"))
            ->select(
                'category',
                DB::raw('COUNT(*) as services_count'),
                DB::raw('COUNT(DISTINCT provider_id) as providers_count')
            )
            ->groupBy('category')
            ->orderByDesc('services_count')
            ->get();

        $categoryNames = $categories->pluck('category')->filter()->values()->all();

        // Cache Unsplash images for 24 hours
        $categoryImages = Cache::remember(
            'category_images_' . md5(json_encode($categoryNames)),
            now()->addDay(),
            fn() => $this->unsplash->getImagesForCategories($categoryNames, 'regular')
        );

        return view('pages.browse', compact('categories', 'categoryImages', 'search'));
    }

    /**
     * Display providers for a specific category
     */
    public function category(Request $request, string $category): View
    {
        $filters = $request->validate([
            'sort' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'city' => 'nullable|string',
            'availability' => 'nullable|in:any,available_today,available_week',
        ]);

        $sort = $filters['sort'] ?? 'popular';
        $availability = $filters['availability'] ?? 'any';

        // Fetch services with provider relationship
        $servicesQuery = Service::query()
            ->where('category', $category)
            ->where('is_active', true)
            ->with(['provider:id,first_name,last_name,name,photo,city,area,bio,expertise,experience_years'])
            ->withCount('bookings')
            ->when($filters['min_price'] ?? null, fn($q, $price) => $q->where('price', '>=', $price))
            ->when($filters['max_price'] ?? null, fn($q, $price) => $q->where('price', '<=', $price));

        $services = $servicesQuery->get();

        // Filter by city
        if (!empty($filters['city'])) {
            $services = $services->filter(fn($service) =>
                $service->provider &&
                str_contains(strtolower($service->provider->city), strtolower($filters['city']))
            )->values();
        }

        if ($availability !== 'any') {
            $slotService = app(SlotGenerationService::class);
            $today = now()->toDateString();

            $services = $services->filter(function ($service) use ($availability, $slotService, $today) {
                if (!$service->provider) {
                    return false;
                }

                if ($availability === 'available_today') {
                    return $slotService->generateAvailableSlots($service->provider->id, $today)->isNotEmpty();
                }

                return $slotService->getAvailableDates($service->provider->id, 7)->isNotEmpty();
            })->values();
        }

        $providerIds = $services->pluck('provider_id')->unique();

        // Preload review stats in a single query
        $reviewStats = Review::whereIn('provider_id', $providerIds)
            ->select(
                'provider_id',
                DB::raw('AVG(rating) as avg_rating'),
                DB::raw('COUNT(*) as review_count')
            )
            ->groupBy('provider_id')
            ->get()
            ->keyBy('provider_id');

        // Group services by provider and calculate stats
        $providers = $services->groupBy('provider_id')->map(function($providerServices) use ($reviewStats) {
            $provider = $providerServices->first()->provider;
            if (!$provider) return null;

            $stats = $reviewStats[$provider->id] ?? null;

            return (object) [
                'user' => $provider,
                'services' => $providerServices,
                'avg_rating' => $stats->avg_rating ?? 0,
                'review_count' => $stats->review_count ?? 0,
                'min_price' => $providerServices->min('price'),
                'total_bookings' => $providerServices->sum('bookings_count'),
                'experience_years' => $provider->experience_years ?? 0
            ];
        })->filter()->values();

        // Sort providers based on selected criteria
        $providers = match ($sort) {
            'rating' => $providers->sortByDesc('avg_rating')->values(),
            'price_low' => $providers->sortBy('min_price')->values(),
            'price_high' => $providers->sortByDesc('min_price')->values(),
            'experience' => $providers->sortByDesc('experience_years')->values(),
            default => $providers->sortByDesc('total_bookings')->values(),
        };

        // Cache category image
        $categoryImage = Cache::remember(
            "category_image_{$category}",
            now()->addDay(),
            fn() => $this->unsplash->getImageForQuery($category . ' service', 'regular')
        );

        // Available cities for filter dropdown
        $availableCities = $services->pluck('provider.city')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('pages.browse_category', [
            'category' => $category,
            'categoryImage' => $categoryImage,
            'providers' => $providers,
            'sort' => $sort,
            'minPrice' => $filters['min_price'] ?? null,
            'maxPrice' => $filters['max_price'] ?? null,
            'city' => $filters['city'] ?? null,
            'availability' => $availability,
            'availableCities' => $availableCities,
        ]);
    }

    /**
     * Return JSON suggestions for live service search.
     */
    public function suggest(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim();

        if ($q->length() < 2) {
            return response()->json([]);
        }

        $query = (string) $q;

        $categories = Service::query()
            ->where('is_active', true)
            ->where('category', 'like', "%{$query}%")
            ->select('category', DB::raw('COUNT(*) as services_count'))
            ->groupBy('category')
            ->orderByDesc('services_count')
            ->limit(6)
            ->get();

        $results = $categories->map(function ($cat) {
            $image = Cache::remember(
                'unsplash_suggest_' . md5($cat->category),
                now()->addDay(),
                fn() => $this->unsplash->getImageForQuery($cat->category . ' service', 'thumb')
            );

            return [
                'name' => $cat->category,
                'count' => $cat->services_count,
                'image' => $image,
                'url' => route('customer.browse.category', ['category' => $cat->category]),
            ];
        });

        return response()->json($results);
    }
}
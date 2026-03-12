<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class UnsplashService
{
    protected string $accessKey;

    public function __construct()
    {
        $this->accessKey = config('services.unsplash.access_key', '');
    }

    public function getImageForQuery(string $query, string $size = 'small'): ?string
    {
        if (empty($this->accessKey)) {
            return null;
        }

        $cacheKey = 'unsplash_' . md5($query . $size);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($query, $size) {
            try {
                $response = Http::timeout(5)->get('https://api.unsplash.com/search/photos', [
                    'query' => $query,
                    'per_page' => 1,
                    'orientation' => 'landscape',
                    'client_id' => $this->accessKey,
                ]);

                if ($response->ok()) {
                    $results = $response->json()['results'] ?? [];
                    if (!empty($results)) {
                        return $results[0]['urls'][$size] ?? $results[0]['urls']['small'] ?? null;
                    }
                }
            } catch (\Throwable $e) {
                // fail silently
            }

            return null;
        });
    }

    /**
     * Get images for multiple categories in one batch.
     *
     * @return array<string, string|null>
     */
    public function getImagesForCategories(array $categories, string $size = 'small'): array
    {
        $images = [];
        foreach ($categories as $category) {
            $images[$category] = $this->getImageForQuery($category . ' service', $size);
        }
        return $images;
    }
}
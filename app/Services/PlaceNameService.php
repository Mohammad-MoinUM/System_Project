<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PlaceNameService
{
    public function getPlaceName(float|string $lat, float|string $lng): string
    {
        $normalizedLat = number_format((float) $lat, 7, '.', '');
        $normalizedLng = number_format((float) $lng, 7, '.', '');
        $cacheKey = "place_{$normalizedLat}_{$normalizedLng}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($normalizedLat, $normalizedLng) {
            /** @var Response $response */
            $response = Http::withHeaders([
                'Accept-Language' => 'en',
                'User-Agent' => 'HaalChaal/1.0 (Laravel Reverse Geocoder)',
            ])->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $normalizedLat,
                'lon' => $normalizedLng,
                'format' => 'json',
            ]);

            if (!$response->ok()) {
                return "{$normalizedLat}, {$normalizedLng}";
            }

            $data = $response->json();
            $city = $data['address']['city']
                ?? $data['address']['town']
                ?? $data['address']['village']
                ?? '';
            $country = $data['address']['country'] ?? '';

            return trim("{$city}, {$country}", ' ,') ?: ($data['display_name'] ?? "{$normalizedLat}, {$normalizedLng}");
        });
    }
}

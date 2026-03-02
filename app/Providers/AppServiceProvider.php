<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $request = request();
        if (!$request->hasSession() || $request->session()->has('currency')) {
            return;
        }

        $defaultCurrency = config('currencies.default', 'BDT');
        $currencyOptions = config('currencies.options', []);
        $currency = $defaultCurrency;

        try {
            $location = geoip($request->ip());
            $detectedCurrency = strtoupper((string) ($location->currency ?? ''));

            if ($detectedCurrency && array_key_exists($detectedCurrency, $currencyOptions)) {
                $currency = $detectedCurrency;
            }
        } catch (\Throwable $e) {
            // Keep default currency when GeoIP fails.
        }

        $request->session()->put('currency', $currency);
    }
}

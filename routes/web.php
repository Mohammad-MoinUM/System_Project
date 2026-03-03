<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AuthPageController;
use App\Http\Controllers\ProviderDashboardController;
use App\Http\Controllers\ProviderPageController;
use App\Http\Controllers\CustomerDashboardController;

// ── Public Pages ─────────────────────────────────────────────
Route::get('/',           [PageController::class, 'home'])->name('home');
Route::get('/services',   [PageController::class, 'services'])->name('services');
Route::get('/how-it-works',[PageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/about',      [PageController::class, 'about'])->name('about');
Route::get('/contact',    [PageController::class, 'contact'])->name('contact');

// ── Auth Pages ───────────────────────────────────────────────
Route::get('/login',    [AuthPageController::class, 'login'])->name('login');
Route::get('/register', [AuthPageController::class, 'register'])->name('register');
Route::post('/login',   [AuthPageController::class, 'loginStore'])->name('login.store');
Route::post('/register',[AuthPageController::class, 'registerStore'])->name('register.store');
Route::post('/logout',  [AuthPageController::class, 'logout'])->name('logout');

// ── Currency Selection ──────────────────────────────────────
Route::post('/currency', function (Request $request) {
    $currency = $request->input('currency', config('currencies.default', 'BDT'));
    $options = config('currencies.options', []);
    if (!array_key_exists($currency, $options)) {
        $currency = config('currencies.default', 'BDT');
    }

    $request->session()->put('currency', $currency);

    return back();
})->name('currency.set');

// ── General Dashboard / Profile / Settings ───────────────────
Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard')->middleware('auth');
Route::get('/profile',   [PageController::class, 'profile'])->name('profile')->middleware('auth');
Route::get('/settings',  [PageController::class, 'settings'])->name('settings')->middleware('auth');

// ── Provider Routes ──────────────────────────────────────────
Route::prefix('provider')->name('provider.')->middleware('auth')->group(function () {
    Route::get('/',          [ProviderDashboardController::class, 'index'])->name('dashboard');
    Route::get('/jobs',      [ProviderPageController::class, 'jobs'])->name('jobs');
    Route::get('/earnings',  [ProviderPageController::class, 'earnings'])->name('earnings');
    Route::get('/reviews',   [ProviderPageController::class, 'reviews'])->name('reviews');
    Route::get('/schedule',  [ProviderPageController::class, 'schedule'])->name('schedule');
    Route::get('/analytics', [ProviderPageController::class, 'analytics'])->name('analytics');
    Route::get('/settings',  [ProviderPageController::class, 'settings'])->name('settings');
});

// ── Customer Routes ─────────────────────────────────────────────
Route::prefix('customer')->name('customer.')->middleware('auth')->group(function () {
    Route::get('/',        [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/browse',  [CustomerDashboardController::class, 'browse'])->name('browse');
    Route::get('/history', [CustomerDashboardController::class, 'history'])->name('history');
});
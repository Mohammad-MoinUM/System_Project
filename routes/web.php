<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AuthPageController;
use App\Http\Controllers\ProviderDashboardController;
use App\Http\Controllers\ProviderPageController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BrowseController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SavedProviderController;
use App\Http\Controllers\ReviewController;

// ── Public Pages ─────────────────────────────────────────────
Route::get('/',           [PageController::class, 'home'])->name('home');
Route::get('/services',   [PageController::class, 'services'])->name('services');
Route::get('/how-it-works',[PageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/about',      [PageController::class, 'about'])->name('about');
Route::get('/contact',    [PageController::class, 'contact'])->name('contact');
Route::post('/contact',   [PageController::class, 'contactStore'])->name('contact.store');

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
Route::get('/profile',      [ProfileController::class, 'show'])->name('profile')->middleware('auth');
Route::get('/profile/edit',  [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth');
Route::put('/profile',       [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');
Route::get('/settings',  [PageController::class, 'settings'])->name('settings')->middleware('auth');

// ── Notifications ────────────────────────────────────────────
Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/',              [NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/read',   [NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('readAll');
});

// ── Provider Routes ──────────────────────────────────────────
Route::prefix('provider')->name('provider.')->middleware(['auth', 'onboarding'])->group(function () {
    Route::get('/',          [ProviderDashboardController::class, 'index'])->name('dashboard');
    Route::get('/jobs',      [ProviderPageController::class, 'jobs'])->name('jobs');
    Route::get('/earnings',  [ProviderPageController::class, 'earnings'])->name('earnings');
    Route::get('/reviews',   [ProviderPageController::class, 'reviews'])->name('reviews');
    Route::get('/schedule',  [ProviderPageController::class, 'schedule'])->name('schedule');
    Route::get('/analytics', [ProviderPageController::class, 'analytics'])->name('analytics');
    Route::get('/settings',  [ProviderPageController::class, 'settings'])->name('settings');

    // ── Provider Service Management ──────────────────────────
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/',             [ServiceController::class, 'index'])->name('index');
        Route::get('/create',       [ServiceController::class, 'create'])->name('create');
        Route::post('/',            [ServiceController::class, 'store'])->name('store');
        Route::get('/{service}/edit', [ServiceController::class, 'edit'])->name('edit');
        Route::put('/{service}',    [ServiceController::class, 'update'])->name('update');
        Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy');
        Route::post('/{service}/toggle', [ServiceController::class, 'toggle'])->name('toggle');
    });
});

// ── Onboarding Routes ───────────────────────────────────────────
Route::middleware('auth')->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/customer',  [OnboardingController::class, 'customerForm'])->name('customer');
    Route::post('/customer', [OnboardingController::class, 'customerStore'])->name('customer.store');
    Route::get('/provider',  [OnboardingController::class, 'providerForm'])->name('provider');
    Route::post('/provider', [OnboardingController::class, 'providerStore'])->name('provider.store');
});

// ── Customer Routes ─────────────────────────────────────────────
Route::prefix('customer')->name('customer.')->middleware(['auth', 'onboarding'])->group(function () {
    Route::get('/',        [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/browse',  [BrowseController::class, 'index'])->name('browse');
    Route::get('/browse/suggest', [BrowseController::class, 'suggest'])->name('browse.suggest');
    Route::get('/browse/{category}', [BrowseController::class, 'category'])->name('browse.category');
    Route::get('/history', [CustomerDashboardController::class, 'history'])->name('history');

    // ── Saved Providers ──────────────────────────────────────
    Route::get('/saved',              [SavedProviderController::class, 'index'])->name('saved');
    Route::post('/saved/{provider}',  [SavedProviderController::class, 'store'])->name('saved.toggle');
    Route::delete('/saved/{provider}',[SavedProviderController::class, 'destroy'])->name('saved.destroy');
});

// ── Booking Routes ──────────────────────────────────────────────
Route::middleware(['auth', 'onboarding'])->group(function () {
    Route::get('/book/{service}',      [BookingController::class, 'create'])->name('booking.create');
    Route::post('/book',               [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/{booking}',   [BookingController::class, 'show'])->name('booking.show');
    Route::post('/booking/{booking}/accept',   [BookingController::class, 'accept'])->name('booking.accept');
    Route::post('/booking/{booking}/reject',   [BookingController::class, 'reject'])->name('booking.reject');
    Route::post('/booking/{booking}/start',    [BookingController::class, 'start'])->name('booking.start');
    Route::post('/booking/{booking}/complete', [BookingController::class, 'complete'])->name('booking.complete');
    Route::post('/booking/{booking}/cancel',   [BookingController::class, 'cancel'])->name('booking.cancel');
});

// ── Review Routes ───────────────────────────────────────────────
Route::middleware(['auth', 'onboarding'])->group(function () {
    Route::post('/review',             [ReviewController::class, 'store'])->name('review.store');
    Route::post('/review/{review}/reply', [ReviewController::class, 'reply'])->name('review.reply');
});
<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileSupportController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [MobileAuthController::class, 'register']);
        Route::post('/login', [MobileAuthController::class, 'login']);

        Route::middleware('mobile.auth')->group(function () {
            Route::post('/logout', [MobileAuthController::class, 'logout']);
            Route::get('/me', [MobileAuthController::class, 'me']);
            Route::put('/profile', [MobileAuthController::class, 'updateProfile']);
            Route::put('/password', [MobileAuthController::class, 'updatePassword']);
        });
    });
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/bookings/{id}/location', [App\Http\Controllers\TrackingController::class, 'updateLocation']);
    Route::get('/bookings/{id}/location',  [App\Http\Controllers\TrackingController::class, 'getLocation']);
});
    Route::middleware('mobile.auth')->prefix('support')->group(function () {
        Route::get('/overview', [MobileSupportController::class, 'overview']);
        Route::get('/payments', [MobileSupportController::class, 'payments']);
        Route::get('/tracking', [MobileSupportController::class, 'tracking']);
        Route::get('/bookings', [MobileSupportController::class, 'bookings']);
        Route::post('/bookings/{booking}/start', [MobileSupportController::class, 'startBooking']);
        Route::post('/bookings/{booking}/request-completion', [MobileSupportController::class, 'requestCompletion']);
        Route::post('/bookings/{booking}/confirm-completion', [MobileSupportController::class, 'confirmCompletion']);
        Route::post('/bookings/{booking}/pay', [MobileSupportController::class, 'payBooking']);
        Route::get('/bookings/{booking}/chat', [MobileSupportController::class, 'bookingChatIndex']);
        Route::post('/bookings/{booking}/chat', [MobileSupportController::class, 'bookingChatStore']);
        Route::post('/bookings/{booking}/tip', [MobileSupportController::class, 'tipBooking']);
        Route::post('/bookings/{booking}/report-issue', [MobileSupportController::class, 'reportIssue']);
        Route::get('/chat', [MobileSupportController::class, 'chatIndex']);
        Route::post('/chat', [MobileSupportController::class, 'chatStore']);
    });
});
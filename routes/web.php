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
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SavedProviderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AdminServiceController;
use App\Http\Controllers\AdminReviewController;
use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\ProviderVerificationController;
use App\Http\Controllers\AdminProviderVerificationController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\AdminSupportController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\CorporateRegistrationController;
use App\Http\Controllers\CorporateDashboardController;
use App\Http\Controllers\CompanyBranchController;
use App\Http\Controllers\CompanyStaffController;
use App\Http\Controllers\CompanyServiceRequestController;
use App\Http\Controllers\CompanyInvoiceController;
use App\Http\Controllers\StaffInvitationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

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

// ── Email Verification ───────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function (Request $request) {
        $devVerificationUrl = null;

        if (
            app()->environment('local')
            && config('mail.default') === 'log'
            && !$request->user()->hasVerifiedEmail()
        ) {
            $devVerificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $request->user()->getKey(),
                    'hash' => sha1($request->user()->getEmailForVerification()),
                ]
            );
        }

        return view('auth.verify-email', compact('devVerificationUrl'));
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('dashboard')->with('success', 'Email verified successfully.');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent.');
    })->middleware('throttle:6,1')->name('verification.send');
});

// ── Staff Invitation Acceptance ──────────────────────────────
Route::get('/staff-invitations/{token}', [StaffInvitationController::class, 'show'])->name('staff-invitations.show');
Route::post('/staff-invitations/{token}', [StaffInvitationController::class, 'accept'])->name('staff-invitations.accept');

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
Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard')->middleware(['auth', 'verified']);
Route::get('/profile',      [ProfileController::class, 'show'])->name('profile')->middleware(['auth', 'verified']);
Route::get('/profile/edit',  [ProfileController::class, 'edit'])->name('profile.edit')->middleware(['auth', 'verified']);
Route::put('/profile',       [ProfileController::class, 'update'])->name('profile.update')->middleware(['auth', 'verified']);
Route::post('/profile/addresses', [ProfileController::class, 'storeAddress'])->name('profile.addresses.store')->middleware(['auth', 'verified']);
Route::post('/profile/addresses/{address}/default', [ProfileController::class, 'setDefaultAddress'])->name('profile.addresses.default')->middleware(['auth', 'verified']);
Route::delete('/profile/addresses/{address}', [ProfileController::class, 'destroyAddress'])->name('profile.addresses.destroy')->middleware(['auth', 'verified']);
Route::post('/profile/rewards/redeem', [ProfileController::class, 'redeemRewards'])->name('profile.rewards.redeem')->middleware(['auth', 'verified']);
Route::get('/settings',  [PageController::class, 'settings'])->name('settings')->middleware(['auth', 'verified']);

// ── Notifications ────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/',              [NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/read',   [NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('readAll');
});

// ── Provider Routes ──────────────────────────────────────────
Route::prefix('provider')->name('provider.')->middleware(['auth', 'onboarding', 'verified'])->group(function () {
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

    // ── Provider Availability Management ─────────────────────
    Route::prefix('availability')->name('availability.')->group(function () {
        Route::get('/',                        [AvailabilityController::class, 'index'])->name('index');
        Route::post('/{availability}',         [AvailabilityController::class, 'update'])->name('update');
        Route::post('/batch/update',           [AvailabilityController::class, 'updateBatch'])->name('update-batch');
        Route::post('/{availability}/toggle',  [AvailabilityController::class, 'toggle'])->name('toggle');
    });
});

// ── Onboarding Routes ───────────────────────────────────────────
Route::middleware('auth')->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/customer',  [OnboardingController::class, 'customerForm'])->name('customer');
    Route::post('/customer', [OnboardingController::class, 'customerStore'])->name('customer.store');
    Route::get('/provider',  [OnboardingController::class, 'providerForm'])->name('provider');
    Route::post('/provider', [OnboardingController::class, 'providerStore'])->name('provider.store');
});

// ── Provider Verification Routes ────────────────────────────────
Route::middleware('auth')->prefix('provider')->name('provider.')->group(function () {
    Route::get('/verification-pending', [ProviderVerificationController::class, 'pending'])->name('verification-pending');
    Route::get('/verification-rejected', [ProviderVerificationController::class, 'rejected'])->name('verification-rejected');
    Route::post('/logout', [ProviderVerificationController::class, 'logout'])->name('logout');
});

// ── Customer Routes ─────────────────────────────────────────────
Route::prefix('customer')->name('customer.')->middleware(['auth', 'verified', 'onboarding'])->group(function () {
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
Route::middleware(['auth', 'verified', 'onboarding'])->group(function () {
    Route::get('/book/{service}',      [BookingController::class, 'create'])->name('booking.create');
    Route::post('/book',               [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/{booking}',   [BookingController::class, 'show'])->name('booking.show');
    Route::get('/booking/{booking}/rebook',   [BookingController::class, 'rebook'])->name('booking.rebook');
    Route::post('/booking/{booking}/rebook-now', [BookingController::class, 'rebookNow'])->name('booking.rebook.now');
    Route::get('/booking/{booking}/tracking', [BookingController::class, 'tracking'])->name('booking.tracking');
    Route::get('/booking/{booking}/receipt', [PaymentController::class, 'receipt'])->name('booking.receipt');
    Route::post('/booking/{booking}/pay', [PaymentController::class, 'pay'])->name('booking.pay');
    Route::post('/booking/{booking}/tip', [PaymentController::class, 'tip'])->name('booking.tip');
    Route::post('/booking/{booking}/complaint', [ComplaintController::class, 'store'])->name('booking.complaint');
    Route::post('/booking/{booking}/cash-collected', [PaymentController::class, 'collectCash'])->name('booking.cash-collected');
    Route::post('/booking/{booking}/refund', [PaymentController::class, 'requestRefund'])->name('booking.refund');
    Route::post('/booking/{booking}/accept',   [BookingController::class, 'accept'])->name('booking.accept');
    Route::post('/booking/{booking}/reject',   [BookingController::class, 'reject'])->name('booking.reject');
    Route::post('/booking/{booking}/start',    [BookingController::class, 'start'])->name('booking.start');
    Route::post('/booking/{booking}/complete', [BookingController::class, 'complete'])->name('booking.complete');
    Route::post('/booking/{booking}/cancel',   [BookingController::class, 'cancel'])->name('booking.cancel');
    Route::post('/booking/{booking}/tracking', [BookingController::class, 'updateTracking'])->name('booking.tracking.update');
});

// ── Availability & Slot AJAX Endpoints ──────────────────────────
Route::prefix('provider')->name('provider.')->group(function () {
    Route::post('/availability/get-slots', [AvailabilityController::class, 'getSlots'])->name('availability.get-slots');
    Route::post('/availability/get-dates', [AvailabilityController::class, 'getAvailableDates'])->name('availability.get-dates');
});

// ── Review Routes ───────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'onboarding'])->group(function () {
    Route::post('/review',             [ReviewController::class, 'store'])->name('review.store');
    Route::post('/review/{review}/reply', [ReviewController::class, 'reply'])->name('review.reply');
});

// ── Support Chat Routes ──────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('support')->name('support.')->group(function () {
    Route::get('/', [SupportController::class, 'index'])->name('index');
    Route::post('/message', [SupportController::class, 'store'])->name('store');
    Route::get('/messages', [SupportController::class, 'messages'])->name('messages');
});

// ── Corporate B2B Routes ──────────────────────────────────────────
Route::prefix('corporate')->name('corporate.')->group(function () {
    // ── Public Registration ──────────────────────────────────
    Route::get('/register', [CorporateRegistrationController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [CorporateRegistrationController::class, 'register'])->name('register.store');

    // ── Authenticated Corporate Routes ───────────────────────
    Route::middleware(['auth', 'verified', 'corporate'])->group(function () {
        // ── Dashboard ────────────────────────────────────────
        Route::get('/', [CorporateDashboardController::class, 'index'])->name('dashboard');
        Route::post('/switch/{companyId}', [CorporateDashboardController::class, 'switchCompany'])->name('switch-company');
        Route::get('/{companyId}/bookings', [CorporateDashboardController::class, 'bookingHistory'])->name('booking-history');
        Route::get('/{companyId}/booking/{bookingId}', [CorporateDashboardController::class, 'bookingDetails'])->name('booking-details');

        // ── Branch Management ────────────────────────────────
        Route::prefix('{companyId}/branches')->name('branches.')->group(function () {
            Route::get('/', [CompanyBranchController::class, 'index'])->name('index');
            Route::get('/create', [CompanyBranchController::class, 'create'])->name('create');
            Route::post('/', [CompanyBranchController::class, 'store'])->name('store');
            Route::get('/{branchId}', [CompanyBranchController::class, 'show'])->name('show');
            Route::get('/{branchId}/edit', [CompanyBranchController::class, 'edit'])->name('edit');
            Route::put('/{branchId}', [CompanyBranchController::class, 'update'])->name('update');
            Route::delete('/{branchId}', [CompanyBranchController::class, 'destroy'])->name('destroy');
        });

        // ── Staff Management ─────────────────────────────────
        Route::prefix('{companyId}/staff')->name('staff.')->group(function () {
            Route::get('/', [CompanyStaffController::class, 'index'])->name('index');
            Route::get('/invite', [CompanyStaffController::class, 'create'])->name('create');
            Route::post('/invite', [CompanyStaffController::class, 'inviteStaff'])->name('store');
            Route::get('/{memberId}/edit', [CompanyStaffController::class, 'edit'])->name('edit');
            Route::put('/{memberId}', [CompanyStaffController::class, 'update'])->name('update');
            Route::delete('/{memberId}', [CompanyStaffController::class, 'destroy'])->name('destroy');
        });

        // ── Service Requests ─────────────────────────────────
        Route::prefix('{companyId}/requests')->name('requests.')->group(function () {
            Route::get('/', [CompanyServiceRequestController::class, 'index'])->name('index');
            Route::get('/create', [CompanyServiceRequestController::class, 'create'])->name('create');
            Route::post('/', [CompanyServiceRequestController::class, 'store'])->name('store');
            Route::get('/{requestId}', [CompanyServiceRequestController::class, 'show'])->name('show');
            Route::get('/{requestId}/approve', [CompanyServiceRequestController::class, 'approvalForm'])->name('approve-form');
            Route::post('/{requestId}/approve', [CompanyServiceRequestController::class, 'approve'])->name('approve');
            Route::post('/{requestId}/reject', [CompanyServiceRequestController::class, 'reject'])->name('reject');
        });

        // ── Invoices ─────────────────────────────────────────
        Route::prefix('{companyId}/invoices')->name('invoices.')->group(function () {
            Route::get('/', [CompanyInvoiceController::class, 'index'])->name('index');
            Route::get('/{invoiceId}', [CompanyInvoiceController::class, 'show'])->name('show');
            Route::get('/{invoiceId}/download', [CompanyInvoiceController::class, 'download'])->name('download');
            Route::post('/generate/{month}/{year}', [CompanyInvoiceController::class, 'generateMonthly'])->name('generate');
        });
    });
});

// ── Admin Routes ─────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    // ── Admin Dashboard ──────────────────────────────────────
    Route::get('/',                    [AdminDashboardController::class, 'index'])->name('dashboard');

    // ── Admin User Management ────────────────────────────────
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',                [AdminUserController::class, 'index'])->name('index');
        Route::get('/{user}',          [AdminUserController::class, 'show'])->name('show');
        Route::get('/{user}/edit',     [AdminUserController::class, 'edit'])->name('edit');
        Route::put('/{user}',          [AdminUserController::class, 'update'])->name('update');
        Route::delete('/{user}',       [AdminUserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('reset-password');
    });
    // ── Admin Creation (Restricted to Admin) ──────────────
    Route::prefix('admin-management')->name('create-admin.')->group(function () {
        Route::get('/create', [AdminManagementController::class, 'createAdmin'])->name('index');
        Route::post('/store', [AdminManagementController::class, 'storeAdmin'])->name('store');
    });
    // ── Admin Booking Management ─────────────────────────────
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/',                [AdminBookingController::class, 'index'])->name('index');
        Route::get('/{booking}',       [AdminBookingController::class, 'show'])->name('show');
        Route::post('/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('cancel');
        Route::post('/refunds/{refundRequest}/approve', [AdminBookingController::class, 'approveRefund'])->name('refunds.approve');
        Route::post('/refunds/{refundRequest}/reject', [AdminBookingController::class, 'rejectRefund'])->name('refunds.reject');
    });

    // ── Admin Service Management ─────────────────────────────
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/',                [AdminServiceController::class, 'index'])->name('index');
        Route::get('/{service}',       [AdminServiceController::class, 'show'])->name('show');
        Route::post('/{service}/toggle', [AdminServiceController::class, 'toggle'])->name('toggle');
        Route::delete('/{service}',    [AdminServiceController::class, 'destroy'])->name('destroy');
    });

    // ── Admin Review Management ──────────────────────────────
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/',                [AdminReviewController::class, 'index'])->name('index');
        Route::get('/{review}',        [AdminReviewController::class, 'show'])->name('show');
        Route::delete('/{review}',     [AdminReviewController::class, 'destroy'])->name('destroy');
    });

    // ── Admin Provider Verification ──────────────────────────
    Route::prefix('providers')->name('providers.')->group(function () {
        Route::get('/pending',         [AdminProviderVerificationController::class, 'pending'])->name('pending');
        Route::get('/approved',        [AdminProviderVerificationController::class, 'approved'])->name('approved');
        Route::get('/rejected',        [AdminProviderVerificationController::class, 'rejected'])->name('rejected');
        Route::get('/{provider}',      [AdminProviderVerificationController::class, 'show'])->name('show');
        Route::post('/{provider}/approve', [AdminProviderVerificationController::class, 'approve'])->name('approve');
        Route::post('/{provider}/reject',  [AdminProviderVerificationController::class, 'reject'])->name('reject');
    });

    // ── Admin Support Inbox ──────────────────────────────────
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [AdminSupportController::class, 'index'])->name('index');
        Route::get('/{conversation}', [AdminSupportController::class, 'show'])->name('show');
        Route::get('/{conversation}/messages', [AdminSupportController::class, 'messages'])->name('messages');
        Route::post('/{conversation}/reply', [AdminSupportController::class, 'reply'])->name('reply');
        Route::post('/{conversation}/close', [AdminSupportController::class, 'close'])->name('close');
        Route::post('/{conversation}/reopen', [AdminSupportController::class, 'reopen'])->name('reopen');
    });
});
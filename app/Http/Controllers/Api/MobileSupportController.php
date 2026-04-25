<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingChatMessage;
use App\Models\Complaint;
use App\Models\Payment;
use App\Models\ServiceProvider;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\EscrowService;
use App\Services\LoyaltyRewardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MobileSupportController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $unreadBookingChats = BookingChatMessage::query()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->whereHas('booking', function ($query) use ($user): void {
                $query->where(function ($bookingQuery) use ($user): void {
                    $bookingQuery
                        ->where('provider_id', $user->id)
                        ->orWhere('taker_id', $user->id);
                });
            })
            ->count();

        return response()->json([
            'message' => 'Mobile support API connected to Laravel.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'modules' => ['payments', 'live_tracking', 'jobs', 'chat'],
            'unread_booking_chats' => $unreadBookingChats,
        ]);
    }

    public function bookings(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $query = Booking::with(['service:id,name', 'provider:id,name', 'taker:id,name'])
            ->withSum(['payments as tip_total' => function ($paymentQuery) {
                $paymentQuery->where('type', 'tip');
            }], 'amount')
            ->withSum(['payments as my_tip_total' => function ($paymentQuery) use ($user) {
                $paymentQuery->where('type', 'tip')->where('user_id', $user->id);
            }], 'amount')
            ->latest('id')
            ->limit(25);

        if ($user->role === 'provider') {
            $query->where('provider_id', $user->id);
        } elseif ($user->role === 'customer') {
            $query->where('taker_id', $user->id);
        }

        $bookings = $query->get()->map(function (Booking $booking) use ($user) {
            $isProvider = (int) $booking->provider_id === (int) $user->id;
            $isCustomer = (int) $booking->taker_id === (int) $user->id;
            $unreadBookingChatCount = BookingChatMessage::query()
                ->where('booking_id', $booking->id)
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->count();
            $dueAmount = $booking->payment_status === 'partial_paid'
                ? (float) ($booking->remaining_amount ?? 0)
                : (float) ($booking->upfront_amount ?? 0);

            if ($dueAmount <= 0 && in_array($booking->payment_status, ['unpaid', 'partial_paid'], true)) {
                $dueAmount = (float) ($booking->remaining_amount ?? 0);
            }

            if ($dueAmount <= 0 && $booking->payment_status === 'unpaid') {
                $dueAmount = (float) ($booking->total ?? 0);
            }

            return [
                'id' => $booking->id,
                'status' => $booking->status,
                'booking_mode' => $booking->booking_mode,
                'payment_method' => $booking->payment_method,
                'payment_status' => $booking->payment_status,
                'upfront_amount' => (float) ($booking->upfront_amount ?? 0),
                'remaining_amount' => (float) ($booking->remaining_amount ?? 0),
                'due_amount' => $dueAmount,
                'total' => (float) $booking->total,
                'tip_total' => (float) ($booking->tip_total ?? 0),
                'total_with_tips' => (float) $booking->total + (float) ($booking->tip_total ?? 0),
                'my_tip_total' => (float) ($booking->my_tip_total ?? 0),
                'unread_booking_chat_count' => $unreadBookingChatCount,
                'service_name' => $booking->service?->name,
                'provider_name' => $booking->provider?->name,
                'customer_name' => $booking->taker?->name,
                'scheduled_at' => optional($booking->scheduled_at)->toIso8601String(),
                'provider_completed_at' => optional($booking->provider_completed_at)->toIso8601String(),
                'customer_confirmed_at' => optional($booking->customer_confirmed_at)->toIso8601String(),
                'can_start_job' => $isProvider && $booking->status === 'active',
                'can_request_completion' => $isProvider && in_array($booking->status, ['active', 'in_progress'], true),
                'can_confirm_completion' => $isCustomer
                    && $booking->status === 'awaiting_confirmation'
                    && ($booking->payment_method === 'cash' || $booking->payment_status === 'paid'),
                'can_report_issue' => $isCustomer && in_array($booking->status, ['awaiting_confirmation', 'completed'], true),
                'can_pay_due' => $isCustomer
                    && $booking->payment_method !== 'cash'
                    && in_array($booking->payment_status, ['unpaid', 'partial_paid'], true)
                    && $dueAmount > 0,
                'can_tip' => $isCustomer && $booking->status === 'completed' && (float) ($booking->my_tip_total ?? 0) <= 0,
                'can_chat' => $isCustomer || $isProvider,
            ];
        });

        return response()->json([
            'role' => $user->role,
            'bookings' => $bookings,
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role === 'provider') {
            $query = Booking::where('provider_id', $user->id);
            $totalEarned = Booking::completedTotalWithTipsForUser('provider_id', $user->id);

            return response()->json([
                'role' => 'provider',
                'total_bookings' => (clone $query)->count(),
                'completed_bookings' => (clone $query)->where('status', 'completed')->count(),
                'pending_payment' => (clone $query)->whereIn('payment_status', ['unpaid', 'partial_paid'])->count(),
                'total_earned' => $totalEarned,
            ]);
        }

        if ($user->role === 'admin') {
            return response()->json([
                'role' => 'admin',
                'total_bookings' => Booking::count(),
                'unpaid_bookings' => Booking::where('payment_status', 'unpaid')->count(),
                'partial_paid_bookings' => Booking::where('payment_status', 'partial_paid')->count(),
                'paid_bookings' => Booking::where('payment_status', 'paid')->count(),
            ]);
        }

        $query = Booking::where('taker_id', $user->id);
        $totalSpent = Booking::completedTotalWithTipsForUser('taker_id', $user->id);

        return response()->json([
            'role' => 'customer',
            'total_bookings' => (clone $query)->count(),
            'pending_payment' => (clone $query)->whereIn('payment_status', ['unpaid', 'partial_paid'])->count(),
            'total_spent' => $totalSpent,
        ]);
    }

    public function tracking(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role === 'provider') {
            $location = ServiceProvider::where('user_id', $user->id)->first();
            $activeBooking = Booking::where('provider_id', $user->id)
                ->whereIn('status', ['active', 'in_progress', 'awaiting_confirmation'])
                ->latest('id')
                ->first();

            return response()->json([
                'role' => 'provider',
                'has_live_location' => (bool) $location,
                'latitude' => $location?->latitude,
                'longitude' => $location?->longitude,
                'updated_at' => optional($location?->updated_at)->toIso8601String(),
                'active_jobs' => Booking::where('provider_id', $user->id)
                    ->whereIn('status', ['active', 'in_progress', 'awaiting_confirmation'])
                    ->count(),
                'active_booking_id' => $activeBooking?->id,
                'active_booking_status' => $activeBooking?->status,
                'can_start_job' => $activeBooking?->status === 'active',
                'can_request_completion' => $activeBooking
                    ? in_array($activeBooking->status, ['active', 'in_progress'], true)
                    : false,
            ]);
        }

        if ($user->role === 'customer') {
            $booking = Booking::where('taker_id', $user->id)
                ->whereIn('status', ['active', 'in_progress', 'awaiting_confirmation'])
                ->latest('id')
                ->first();

            $providerLocation = $booking ? ServiceProvider::where('user_id', $booking->provider_id)->first() : null;

            $serviceLocationQuery = null;

            if ($booking) {
                $serviceLocationParts = array_filter([
                    $booking->service_address_label,
                    $booking->service_address_line1,
                    $booking->service_address_line2,
                    $booking->service_area,
                    $booking->service_city,
                    $booking->service_postal_code,
                ]);

                $serviceLocationQuery = $serviceLocationParts ? implode(', ', $serviceLocationParts) : null;
            }

            return response()->json([
                'role' => 'customer',
                'active_booking_id' => $booking?->id,
                'active_booking_status' => $booking?->status,
                'provider_id' => $booking?->provider_id,
                'provider_latitude' => $providerLocation?->latitude,
                'provider_longitude' => $providerLocation?->longitude,
                'provider_location_updated_at' => optional($providerLocation?->updated_at)->toIso8601String(),
                'provider_completed_at' => optional($booking?->provider_completed_at)->toIso8601String(),
                'service_location_query' => $serviceLocationQuery,
                'can_confirm_completion' => $booking
                    ? ($booking->status === 'awaiting_confirmation'
                        && ($booking->payment_method === 'cash' || $booking->payment_status === 'paid'))
                    : false,
            ]);
        }

        return response()->json([
            'role' => 'admin',
            'providers_with_live_location' => ServiceProvider::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->count(),
            'active_jobs' => Booking::whereIn('status', ['active', 'in_progress', 'awaiting_confirmation'])->count(),
        ]);
    }

    public function startBooking(Request $request, Booking $booking): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'provider' || (int) $booking->provider_id !== (int) $user->id) {
            return response()->json(['message' => 'You are not allowed to start this booking.'], 403);
        }

        if ($booking->status !== 'active') {
            return response()->json(['message' => 'Only active bookings can be started.'], 422);
        }

        $booking->update(['status' => 'in_progress']);
        $booking->refresh();

        return response()->json([
            'message' => 'Booking marked as in progress.',
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
            ],
        ]);
    }

    public function requestCompletion(Request $request, Booking $booking): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'provider' || (int) $booking->provider_id !== (int) $user->id) {
            return response()->json(['message' => 'You are not allowed to request completion for this booking.'], 403);
        }

        if (!in_array($booking->status, ['active', 'in_progress'], true)) {
            return response()->json(['message' => 'This booking cannot be marked for completion right now.'], 422);
        }

        $completedImmediately = false;

        DB::transaction(function () use ($booking, &$completedImmediately): void {
            if ($booking->payment_method !== 'cash') {
                $booking->update([
                    'status' => 'awaiting_confirmation',
                    'provider_completed_at' => now(),
                    'escrow_status' => 'held',
                ]);

                return;
            }

            $completedImmediately = true;

            $booking->update(['status' => 'completed']);

            if ($booking->payment_method === 'cash' && $booking->payment_status !== 'paid') {
                $providerWallet = Wallet::firstOrCreate(
                    ['user_id' => $booking->provider_id],
                    ['balance' => 0, 'cashback_balance' => 0]
                );

                $collectionAmount = (float) $booking->total;

                Payment::create([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->taker_id,
                    'method' => 'cash',
                    'type' => 'cash_on_service',
                    'amount' => $collectionAmount,
                    'status' => 'captured',
                    'reference' => 'CASH-' . $booking->id . '-' . now()->format('His'),
                    'captured_at' => now(),
                ]);

                $providerWallet->increment('balance', $collectionAmount);

                WalletTransaction::create([
                    'wallet_id' => $providerWallet->id,
                    'user_id' => $booking->provider_id,
                    'booking_id' => $booking->id,
                    'type' => 'cash_collection_credit',
                    'payment_method' => 'cash',
                    'amount' => $collectionAmount,
                    'balance_after' => (float) $providerWallet->fresh()->balance,
                    'description' => 'Cash collected automatically when booking completed',
                ]);

                $booking->update([
                    'payment_status' => 'paid',
                    'remaining_amount' => 0,
                    'paid_at' => now(),
                    'receipt_number' => $booking->receipt_number ?: 'CASH-' . $booking->id . '-' . strtoupper(substr(md5((string) $booking->id . now()->timestamp), 0, 8)),
                    'escrow_status' => 'not_required',
                    'provider_completed_at' => now(),
                ]);
            }

            if (in_array($booking->payment_status, ['paid', 'partial_paid'], true) && !$booking->cashback_credited_at) {
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $booking->taker_id],
                    ['balance' => 0, 'cashback_balance' => 0]
                );

                $wallet->increment('balance', (float) $booking->cashback_amount);
                $wallet->increment('cashback_balance', (float) $booking->cashback_amount);

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'user_id' => $booking->taker_id,
                    'booking_id' => $booking->id,
                    'type' => 'cashback',
                    'amount' => (float) $booking->cashback_amount,
                    'balance_after' => (float) $wallet->fresh()->balance,
                    'description' => 'Cashback credited after service completion',
                ]);

                $booking->update(['cashback_credited_at' => now()]);
            }
        });

        if ($completedImmediately) {
            app(LoyaltyRewardService::class)->awardForCompletedBooking($booking);
        }

        $booking->refresh();

        return response()->json([
            'message' => $completedImmediately
                ? 'Booking marked as completed.'
                : 'Completion request sent. Waiting for customer confirmation.',
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'provider_completed_at' => optional($booking->provider_completed_at)->toIso8601String(),
                'customer_confirmed_at' => optional($booking->customer_confirmed_at)->toIso8601String(),
            ],
        ]);
    }

    public function confirmCompletion(Request $request, Booking $booking, EscrowService $escrowService): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'customer' || (int) $booking->taker_id !== (int) $user->id) {
            return response()->json(['message' => 'You are not allowed to confirm this booking.'], 403);
        }

        if ($booking->status !== 'awaiting_confirmation') {
            return response()->json(['message' => 'This booking is not waiting for confirmation.'], 422);
        }

        if ($booking->payment_method !== 'cash' && $booking->payment_status !== 'paid') {
            return response()->json(['message' => 'Please complete payment before confirming service completion.'], 422);
        }

        DB::transaction(function () use ($booking, $escrowService, $user): void {
            $releasedAmount = $escrowService->releaseHeldPayments($booking, $user);

            $booking->update([
                'status' => 'completed',
                'customer_confirmed_at' => now(),
                'escrow_status' => 'released',
            ]);

            if ($releasedAmount <= 0 && $booking->payment_method !== 'cash' && in_array($booking->payment_status, ['paid', 'partial_paid'], true)) {
                $booking->refresh();
                Log::warning('Wallet credit edge case: Payment released with zero amount for booking ' . $booking->id . ', payment_status=' . $booking->payment_status);
            }

            if (in_array($booking->payment_status, ['paid', 'partial_paid'], true) && !$booking->cashback_credited_at) {
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $booking->taker_id],
                    ['balance' => 0, 'cashback_balance' => 0]
                );

                $wallet->increment('balance', (float) $booking->cashback_amount);
                $wallet->increment('cashback_balance', (float) $booking->cashback_amount);

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'user_id' => $booking->taker_id,
                    'booking_id' => $booking->id,
                    'type' => 'cashback',
                    'amount' => (float) $booking->cashback_amount,
                    'balance_after' => (float) $wallet->fresh()->balance,
                    'description' => 'Cashback credited after customer confirmed completion',
                ]);

                $booking->update(['cashback_credited_at' => now()]);
            }

            app(LoyaltyRewardService::class)->awardForCompletedBooking($booking);
        });

        $booking->refresh();

        return response()->json([
            'message' => 'Service completion confirmed successfully.',
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'provider_completed_at' => optional($booking->provider_completed_at)->toIso8601String(),
                'customer_confirmed_at' => optional($booking->customer_confirmed_at)->toIso8601String(),
            ],
        ]);
    }

    public function bookingChatIndex(Request $request, Booking $booking): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ((int) $booking->provider_id !== (int) $user->id && (int) $booking->taker_id !== (int) $user->id) {
            return response()->json(['message' => 'You are not allowed to access this booking chat.'], 403);
        }

        $messages = BookingChatMessage::where('booking_id', $booking->id)
            ->with('sender:id,name,role')
            ->oldest()
            ->limit(200)
            ->get();

        BookingChatMessage::where('booking_id', $booking->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'service_name' => $booking->service?->name,
            ],
            'messages' => $messages->map(function (BookingChatMessage $message) use ($user) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender?->name ?? 'User',
                    'sender_role' => $message->sender?->role,
                    'is_mine' => (int) $message->sender_id === (int) $user->id,
                    'message' => $message->message,
                    'attachment_path' => $message->attachment_path,
                    'sent_at' => optional($message->created_at)->toIso8601String(),
                ];
            }),
        ]);
    }

    public function bookingChatStore(Request $request, Booking $booking): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ((int) $booking->provider_id !== (int) $user->id && (int) $booking->taker_id !== (int) $user->id) {
            return response()->json(['message' => 'You are not allowed to send messages for this booking.'], 403);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $created = BookingChatMessage::create([
            'booking_id' => $booking->id,
            'sender_id' => $user->id,
            'message' => $data['message'],
            'attachment_path' => null,
            'is_read' => false,
        ]);

        return response()->json([
            'message' => 'Message sent.',
            'item' => [
                'id' => $created->id,
                'sender_id' => $created->sender_id,
                'sender_name' => $user->name,
                'sender_role' => $user->role,
                'is_mine' => true,
                'message' => $created->message,
                'attachment_path' => $created->attachment_path,
                'sent_at' => optional($created->created_at)->toIso8601String(),
            ],
        ], 201);
    }

    public function payBooking(Request $request, Booking $booking): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'customer' || (int) $booking->taker_id !== (int) $user->id) {
            return response()->json(['message' => 'You are not allowed to pay for this booking.'], 403);
        }

        if ($booking->payment_method === 'cash') {
            return response()->json(['message' => 'Cash bookings are collected after service completion.'], 422);
        }

        $data = $request->validate([
            'payment_method' => ['required', 'in:bkash,nagad,card,wallet'],
        ]);

        $amountDue = $booking->payment_status === 'partial_paid'
            ? (float) ($booking->remaining_amount ?? 0)
            : (float) ($booking->upfront_amount ?? 0);

        if ($amountDue <= 0 && in_array($booking->payment_status, ['unpaid', 'partial_paid'], true)) {
            $amountDue = (float) ($booking->remaining_amount ?? 0);
        }

        if ($amountDue <= 0 && $booking->payment_status === 'unpaid') {
            $amountDue = (float) ($booking->total ?? 0);
        }

        if ($amountDue <= 0) {
            return response()->json(['message' => 'There is no payment due for this booking.'], 422);
        }

        $shouldHoldInEscrow = $booking->status !== 'completed' && $booking->payment_method !== 'cash';

        try {
            DB::transaction(function () use ($booking, $data, $amountDue, $shouldHoldInEscrow, $user): void {
                if ($data['payment_method'] === 'wallet') {
                    $wallet = Wallet::firstOrCreate(
                        ['user_id' => $user->id],
                        ['balance' => 0, 'cashback_balance' => 0]
                    );

                    if ((float) $wallet->balance < $amountDue) {
                        throw new \RuntimeException('Your wallet balance is not enough for this payment.');
                    }

                    $wallet->decrement('balance', $amountDue);

                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'user_id' => $user->id,
                        'booking_id' => $booking->id,
                        'type' => 'debit',
                        'payment_method' => 'wallet',
                        'amount' => $amountDue,
                        'balance_after' => (float) $wallet->fresh()->balance,
                        'description' => 'Booking payment via wallet from mobile app',
                    ]);
                }

                Payment::create([
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'method' => $data['payment_method'],
                    'type' => $booking->payment_status === 'partial_paid' ? 'remaining' : 'upfront',
                    'amount' => $amountDue,
                    'status' => $shouldHoldInEscrow ? 'held' : 'captured',
                    'reference' => strtoupper(substr($data['payment_method'], 0, 3)) . '-' . $booking->id . '-' . now()->format('His'),
                    'captured_at' => now(),
                    'released_at' => $shouldHoldInEscrow ? null : now(),
                    'released_by_user_id' => $shouldHoldInEscrow ? null : $user->id,
                    'metadata' => [
                        'booking_mode' => $booking->booking_mode,
                        'payment_split_type' => $booking->payment_split_type,
                        'escrow' => $shouldHoldInEscrow,
                        'source' => 'mobile',
                    ],
                ]);

                if (!$shouldHoldInEscrow) {
                    $providerWallet = Wallet::firstOrCreate(
                        ['user_id' => $booking->provider_id],
                        ['balance' => 0, 'cashback_balance' => 0]
                    );

                    $providerWallet->increment('balance', $amountDue);

                    WalletTransaction::create([
                        'wallet_id' => $providerWallet->id,
                        'user_id' => $booking->provider_id,
                        'booking_id' => $booking->id,
                        'type' => 'booking_credit',
                        'payment_method' => $data['payment_method'],
                        'amount' => $amountDue,
                        'balance_after' => (float) $providerWallet->fresh()->balance,
                        'description' => 'Booking payment credited to provider wallet from mobile app',
                    ]);
                }

                if ($booking->payment_split_type === 'partial' && $booking->payment_status !== 'partial_paid' && (float) ($booking->remaining_amount ?? 0) > 0) {
                    $booking->update([
                        'payment_status' => 'partial_paid',
                        'remaining_amount' => max((float) $booking->total - $amountDue, 0),
                        'paid_at' => now(),
                    ]);
                } else {
                    $booking->update([
                        'payment_status' => 'paid',
                        'remaining_amount' => 0,
                        'paid_at' => now(),
                        'receipt_number' => $booking->receipt_number ?: 'PAY-' . $booking->id . '-' . strtoupper(substr(md5((string) $booking->id . now()->timestamp), 0, 8)),
                    ]);
                }

                if ($shouldHoldInEscrow) {
                    $booking->forceFill([
                        'escrow_status' => 'held',
                        'escrow_released_at' => null,
                    ])->save();
                }
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $booking->refresh();

        return response()->json([
            'message' => $shouldHoldInEscrow
                ? 'Payment recorded and held in escrow until completion.'
                : 'Payment recorded successfully.',
            'booking' => [
                'id' => $booking->id,
                'payment_status' => $booking->payment_status,
                'remaining_amount' => (float) ($booking->remaining_amount ?? 0),
                'paid_at' => optional($booking->paid_at)->toIso8601String(),
            ],
        ]);
    }

    public function tipBooking(Request $request, Booking $booking): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'customer' || (int) $booking->taker_id !== (int) $user->id) {
            return response()->json(['message' => 'You are not allowed to tip for this booking.'], 403);
        }

        if ($booking->status !== 'completed') {
            return response()->json(['message' => 'Tips are available after the service is completed.'], 422);
        }

        if (Payment::where('booking_id', $booking->id)
            ->where('user_id', $user->id)
            ->where('type', 'tip')
            ->exists()) {
            return response()->json(['message' => 'You already tipped this provider for this booking.'], 422);
        }

        $data = $request->validate([
            'payment_method' => ['required', 'in:bkash,nagad,card,wallet'],
            'tip_amount' => ['required', 'numeric', 'min:1', 'max:50000'],
        ]);

        $tipAmount = round((float) $data['tip_amount'], 2);

        try {
            DB::transaction(function () use ($booking, $user, $data, $tipAmount): void {
                if ($data['payment_method'] === 'wallet') {
                    $payerWallet = Wallet::firstOrCreate(
                        ['user_id' => $user->id],
                        ['balance' => 0, 'cashback_balance' => 0]
                    );

                    if ((float) $payerWallet->balance < $tipAmount) {
                        throw new \RuntimeException('Your wallet balance is not enough for this tip.');
                    }

                    $payerWallet->decrement('balance', $tipAmount);
                    WalletTransaction::create([
                        'wallet_id' => $payerWallet->id,
                        'user_id' => $user->id,
                        'booking_id' => $booking->id,
                        'type' => 'debit',
                        'payment_method' => 'wallet',
                        'amount' => $tipAmount,
                        'balance_after' => (float) $payerWallet->fresh()->balance,
                        'description' => 'Tip paid to provider via wallet',
                    ]);
                }

                Payment::create([
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'method' => $data['payment_method'],
                    'type' => 'tip',
                    'amount' => $tipAmount,
                    'status' => 'captured',
                    'reference' => 'TIP-' . $booking->id . '-' . now()->format('His'),
                    'captured_at' => now(),
                    'metadata' => [
                        'provider_id' => $booking->provider_id,
                        'source' => 'mobile',
                    ],
                ]);

                $providerWallet = Wallet::firstOrCreate(
                    ['user_id' => $booking->provider_id],
                    ['balance' => 0, 'cashback_balance' => 0]
                );

                $providerWallet->increment('balance', $tipAmount);

                WalletTransaction::create([
                    'wallet_id' => $providerWallet->id,
                    'user_id' => $booking->provider_id,
                    'booking_id' => $booking->id,
                    'type' => 'tip_credit',
                    'payment_method' => $data['payment_method'],
                    'amount' => $tipAmount,
                    'balance_after' => (float) $providerWallet->fresh()->balance,
                    'description' => 'Tip received from customer via mobile',
                ]);
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Tip sent successfully.',
            'tip_amount' => $tipAmount,
        ], 201);
    }

    public function reportIssue(Request $request, Booking $booking): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'customer' || (int) $booking->taker_id !== (int) $user->id) {
            return response()->json(['message' => 'You are not allowed to report an issue for this booking.'], 403);
        }

        if (!in_array($booking->status, ['awaiting_confirmation', 'completed'], true)) {
            return response()->json(['message' => 'Issue reporting is available for completion-stage bookings only.'], 422);
        }

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'details' => ['required', 'string', 'min:20', 'max:3000'],
        ]);

        $existingOpenComplaint = Complaint::where('booking_id', $booking->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['submitted', 'in_review'])
            ->exists();

        if ($existingOpenComplaint) {
            return response()->json(['message' => 'You already have an active issue for this booking.'], 422);
        }

        $complaint = Complaint::create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'provider_id' => $booking->provider_id,
            'subject' => $data['subject'],
            'details' => $data['details'],
            'evidence_paths' => null,
            'status' => 'submitted',
        ]);

        return response()->json([
            'message' => 'Issue reported successfully. Support team will review it.',
            'complaint' => [
                'id' => $complaint->id,
                'status' => $complaint->status,
            ],
        ], 201);
    }

    public function chatIndex(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $conversation = SupportConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['status' => 'open']
        );

        $conversation->messages()
            ->where('is_read', false)
            ->where('sender_id', '!=', $user->id)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $messages = $conversation->messages()
            ->with('sender:id,name,role')
            ->oldest()
            ->limit(100)
            ->get();

        return response()->json([
            'conversation_id' => $conversation->id,
            'status' => $conversation->status,
            'messages' => $messages->map(function (SupportMessage $message) use ($user) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender?->name ?? 'Support',
                    'sender_role' => $message->sender?->role,
                    'is_mine' => (int) $message->sender_id === (int) $user->id,
                    'message' => $message->message,
                    'sent_at' => optional($message->created_at)->toIso8601String(),
                ];
            }),
        ]);
    }

    public function chatStore(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $conversation = SupportConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['status' => 'open']
        );

        $created = $conversation->messages()->create([
            'sender_id' => $user->id,
            'message' => $data['message'],
            'is_read' => false,
            'read_at' => null,
        ]);

        $conversation->update([
            'status' => 'open',
            'last_message_at' => now(),
            'last_message_by' => $user->id,
        ]);

        return response()->json([
            'message' => 'Message sent.',
            'item' => [
                'id' => $created->id,
                'sender_id' => $created->sender_id,
                'sender_name' => $user->name,
                'sender_role' => $user->role,
                'is_mine' => true,
                'message' => $created->message,
                'sent_at' => optional($created->created_at)->toIso8601String(),
            ],
        ], 201);
    }
}
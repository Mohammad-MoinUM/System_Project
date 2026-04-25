<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class EscrowService
{
    public function releaseHeldPayments(Booking $booking, User $releasedBy): float
    {
        return DB::transaction(function () use ($booking, $releasedBy): float {
            $booking->loadMissing('payments');

            $heldPayments = $booking->payments()
                ->where('status', 'held')
                ->lockForUpdate()
                ->get();

            if ($heldPayments->isEmpty()) {
                return $this->ensureProviderWalletCredited($booking, $releasedBy);
            }

            $releaseAmount = (float) $heldPayments->sum('amount');

            $providerWallet = Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $booking->provider_id],
                ['balance' => 0, 'cashback_balance' => 0]
            );

            $providerWallet->increment('balance', $releaseAmount);

            foreach ($heldPayments as $payment) {
                $payment->update([
                    'status' => 'released',
                    'released_at' => now(),
                    'released_by_user_id' => $releasedBy->id,
                ]);
            }

            WalletTransaction::create([
                'wallet_id' => $providerWallet->id,
                'user_id' => $booking->provider_id,
                'booking_id' => $booking->id,
                'type' => 'escrow_release',
                'amount' => $releaseAmount,
                'balance_after' => (float) $providerWallet->fresh()->balance,
                'description' => 'Escrow released after customer confirmation',
            ]);

            $booking->forceFill([
                'escrow_status' => 'released',
                'escrow_released_at' => now(),
            ])->save();

            return $releaseAmount;
        });
    }

    private function ensureProviderWalletCredited(Booking $booking, User $releasedBy): float
    {
        // Check if wallet already credited to prevent duplicate credits
        $existingRelease = WalletTransaction::where('booking_id', $booking->id)
            ->where('user_id', $booking->provider_id)
            ->whereIn('type', ['escrow_release', 'booking_credit'])
            ->lockForUpdate()
            ->first();

        if ($existingRelease) {
            return (float) $existingRelease->amount;
        }

        // Try to find any payment records (held, captured, or already released)
        // Exclude tips and refunds - only count main booking payments
        $releaseAmount = (float) $booking->payments()
            ->whereNotIn('type', ['tip', 'refund'])
            ->whereIn('status', ['captured', 'held', 'released'])
            ->sum('amount');

        // Fallback: If booking is marked paid but no payments found in DB,
        // credit the total amount. This handles edge cases where payment was
        // processed externally or status tracking failed.
        if ($releaseAmount <= 0 && in_array($booking->payment_status, ['paid', 'partial_paid'], true)) {
            $releaseAmount = (float) $booking->total;
        }

        if ($releaseAmount <= 0) {
            return 0.0;
        }

        $providerWallet = Wallet::lockForUpdate()->firstOrCreate(
            ['user_id' => $booking->provider_id],
            ['balance' => 0, 'cashback_balance' => 0]
        );

        $providerWallet->increment('balance', $releaseAmount);

        WalletTransaction::create([
            'wallet_id' => $providerWallet->id,
            'user_id' => $booking->provider_id,
            'booking_id' => $booking->id,
            'type' => 'escrow_release',
            'amount' => $releaseAmount,
            'balance_after' => (float) $providerWallet->fresh()->balance,
            'description' => 'Escrow released after customer confirmation',
        ]);

        $booking->forceFill([
            'escrow_status' => 'released',
            'escrow_released_at' => now(),
        ])->save();

        return $releaseAmount;
    }
}
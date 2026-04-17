<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\LoyaltyTransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class LoyaltyRewardService
{
    public function awardForCompletedBooking(Booking $booking): void
    {
        DB::transaction(function () use ($booking): void {
            $booking->loadMissing('taker');

            $earnedPoints = max(1, (int) floor(((float) $booking->total) / 10));
            $this->creditPoints(
                user: $booking->taker,
                points: $earnedPoints,
                type: 'booking_reward',
                booking: $booking,
                referredUserId: null,
                description: 'Points earned from booking #' . $booking->id,
            );

            $completedBookings = Booking::where('taker_id', $booking->taker_id)
                ->where('status', 'completed')
                ->count();

            if (
                $completedBookings === 1
                && $booking->taker->referred_by_user_id
                && !$booking->taker->referral_reward_claimed_at
            ) {
                $referrer = User::find($booking->taker->referred_by_user_id);

                if ($referrer) {
                    $bonusPoints = 50;

                    $this->creditPoints(
                        user: $booking->taker,
                        points: $bonusPoints,
                        type: 'referral_bonus',
                        booking: $booking,
                        referredUserId: $referrer->id,
                        description: 'Referral bonus for completing the first booking',
                    );

                    $this->creditPoints(
                        user: $referrer,
                        points: $bonusPoints,
                        type: 'referral_bonus',
                        booking: $booking,
                        referredUserId: $booking->taker_id,
                        description: 'Referral bonus for inviting ' . $booking->taker->name,
                    );

                    $booking->taker->forceFill([
                        'referral_reward_claimed_at' => now(),
                    ])->save();
                }
            }
        });
    }

    public function redeemPoints(User $user, int $points): array
    {
        return DB::transaction(function () use ($user, $points): array {
            if ($points < 10 || $points % 10 !== 0) {
                throw new \InvalidArgumentException('Points must be redeemed in multiples of 10.');
            }

            $user->refresh();

            if ($user->loyalty_points < $points) {
                throw new \RuntimeException('You do not have enough points to redeem that amount.');
            }

            $creditAmount = round($points / 10, 2);

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'cashback_balance' => 0]
            );

            $wallet->increment('balance', $creditAmount);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => 'reward_redemption',
                'amount' => $creditAmount,
                'balance_after' => (float) $wallet->fresh()->balance,
                'description' => 'Reward points redeemed for wallet credit',
            ]);

            $user->decrement('loyalty_points', $points);

            LoyaltyTransaction::create([
                'user_id' => $user->id,
                'type' => 'redeem',
                'points' => -$points,
                'description' => 'Redeemed points for wallet credit',
            ]);

            return [
                'points' => $points,
                'credit_amount' => $creditAmount,
            ];
        });
    }

    protected function creditPoints(
        User $user,
        int $points,
        string $type,
        ?Booking $booking = null,
        ?int $referredUserId = null,
        ?string $description = null
    ): void {
        $user->increment('loyalty_points', $points);

        LoyaltyTransaction::create([
            'user_id' => $user->id,
            'booking_id' => $booking?->id,
            'referred_user_id' => $referredUserId,
            'type' => $type,
            'points' => $points,
            'description' => $description,
        ]);
    }
}
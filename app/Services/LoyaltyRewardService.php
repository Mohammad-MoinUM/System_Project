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
    public function awardReferralReward(User $user): void
    {
        if (!$user->referred_by_user_id || $user->referral_reward_claimed_at) {
            return;
        }

        DB::transaction(function () use ($user): void {
            $user->refresh();

            if (!$user->referred_by_user_id || $user->referral_reward_claimed_at) {
                return;
            }

            $referrer = User::find($user->referred_by_user_id);
            if (!$referrer) {
                return;
            }

            $rewardMap = [
                'customer' => ['referrer' => 25, 'referred' => 10],
                'provider' => ['referrer' => 50, 'referred' => 20],
            ];

            $rewards = $rewardMap[$user->role] ?? ['referrer' => 25, 'referred' => 10];

            $this->creditWallet(
                user: $referrer,
                amount: $rewards['referrer'],
                booking: null,
                type: 'referral_credit',
                description: 'Referral reward for inviting ' . $user->name,
                metadata: ['referred_user_id' => $user->id, 'role' => $user->role]
            );

            $this->creditWallet(
                user: $user,
                amount: $rewards['referred'],
                booking: null,
                type: 'referral_credit',
                description: 'Welcome credit for joining with a referral code',
                metadata: ['referrer_user_id' => $referrer->id, 'role' => $user->role]
            );

            $user->forceFill([
                'referral_reward_claimed_at' => now(),
            ])->save();
        });
    }

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

            $this->awardReferralReward($booking->taker);
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

    protected function creditWallet(
        User $user,
        float $amount,
        ?Booking $booking,
        string $type,
        string $description,
        array $metadata = []
    ): void {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'cashback_balance' => 0]
        );

        $wallet->increment('balance', $amount);

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'user_id' => $user->id,
            'booking_id' => $booking?->id,
            'type' => $type,
            'amount' => $amount,
            'balance_after' => (float) $wallet->fresh()->balance,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
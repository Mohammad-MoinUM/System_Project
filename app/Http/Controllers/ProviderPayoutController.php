<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ProviderPayoutRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Illuminate\View\View;

class ProviderPayoutController extends Controller
{
    public function index(): View
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => Auth::id()],
            ['balance' => 0, 'cashback_balance' => 0]
        );

        $requests = ProviderPayoutRequest::where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        $transactions = WalletTransaction::where('user_id', Auth::id())
            ->latest()
            ->paginate(15, ['*'], 'tx_page');

        // Cash on service balance - unpaid cash jobs
        $cashOnServiceBalance = (float) Booking::query()
            ->where('provider_id', Auth::id())
            ->where('status', 'completed')
            ->where('payment_method', 'cash')
            ->whereDoesntHave('payments', function ($query) {
                $query->where('type', 'cash_on_service')
                    ->where('status', 'captured');
            })
            ->sum('total');

        // Total payouts already withdrawn (approved + paid + pending)
        $totalPayoutsWithdrawn = (float) ProviderPayoutRequest::where('user_id', Auth::id())
            ->whereIn('status', ['approved', 'paid', 'pending'])
            ->sum('amount');

        // Total Earnings = Current Balance + All Withdrawals Made + Unpaid Cash
        $totalEarnings = (float) $wallet->balance + $totalPayoutsWithdrawn + $cashOnServiceBalance;

        return view('pages.provider.payouts', compact('wallet', 'requests', 'transactions', 'cashOnServiceBalance', 'totalEarnings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'payout_method' => ['required', 'in:bkash,nagad,bank'],
            'account_name' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'string', 'max:120'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'bank_branch' => ['nullable', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['payout_method'] === 'bank' && empty($data['bank_name'])) {
            return back()->with('error', 'Bank name is required for bank payout method.');
        }

        $amount = round((float) $data['amount'], 2);

        try {
            DB::transaction(function () use ($data, $amount) {
                $wallet = Wallet::lockForUpdate()->firstOrCreate(
                    ['user_id' => Auth::id()],
                    ['balance' => 0, 'cashback_balance' => 0]
                );

                if ((float) $wallet->balance < $amount) {
                    throw new RuntimeException('Insufficient wallet balance for withdrawal.');
                }

                $wallet->decrement('balance', $amount);
                $wallet->refresh();

                ProviderPayoutRequest::create([
                    'user_id' => Auth::id(),
                    'wallet_id' => $wallet->id,
                    'payout_method' => $data['payout_method'],
                    'account_name' => $data['account_name'],
                    'account_number' => $data['account_number'],
                    'bank_name' => $data['bank_name'] ?? null,
                    'bank_branch' => $data['bank_branch'] ?? null,
                    'amount' => $amount,
                    'status' => 'pending',
                    'notes' => $data['notes'] ?? null,
                ]);

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'user_id' => Auth::id(),
                    'booking_id' => null,
                    'type' => 'withdrawal_request',
                    'payment_method' => $data['payout_method'],
                    'amount' => $amount,
                    'balance_after' => (float) $wallet->balance,
                    'description' => 'Withdrawal requested via ' . $data['payout_method'],
                ]);
            });
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payout request submitted successfully.');
    }
}

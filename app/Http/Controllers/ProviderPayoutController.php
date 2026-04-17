<?php

namespace App\Http\Controllers;

use App\Models\ProviderPayoutRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        return view('pages.provider.payouts', compact('wallet', 'requests'));
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

        DB::transaction(function () use ($data, $amount) {
            $wallet = Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => Auth::id()],
                ['balance' => 0, 'cashback_balance' => 0]
            );

            if ((float) $wallet->balance < $amount) {
                abort(422, 'Insufficient wallet balance for withdrawal.');
            }

            $wallet->decrement('balance', $amount);
            $updatedWallet = $wallet->fresh();

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
                'amount' => $amount,
                'balance_after' => (float) $updatedWallet->balance,
                'description' => 'Withdrawal requested via ' . $data['payout_method'],
            ]);
        });

        return back()->with('success', 'Payout request submitted successfully.');
    }
}

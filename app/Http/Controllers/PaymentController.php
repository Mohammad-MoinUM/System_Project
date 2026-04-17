<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\RefundRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function pay(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->taker_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->payment_method === 'cash') {
            return back()->with('error', 'Cash bookings are collected after the service is completed.');
        }

        $data = $request->validate([
            'payment_method' => ['required', 'in:bkash,nagad,card,wallet'],
        ]);

        $amountDue = $booking->payment_status === 'partial_paid'
            ? (float) $booking->remaining_amount
            : (float) $booking->upfront_amount;

        if ($amountDue <= 0) {
            return back()->with('error', 'There is no payment due for this booking.');
        }

        $wallet = null;
        if ($data['payment_method'] === 'wallet') {
            $wallet = $this->walletFor(Auth::id());
            if ((float) $wallet->balance < $amountDue) {
                return back()->with('error', 'Your wallet balance is not enough for this payment.');
            }

            $wallet->decrement('balance', $amountDue);
            $this->recordWalletTransaction($wallet, $booking, 'debit', $amountDue, 'Booking payment via wallet');
        }

        Payment::create([
            'booking_id' => $booking->id,
            'user_id' => Auth::id(),
            'method' => $data['payment_method'],
            'type' => $booking->payment_status === 'partial_paid' ? 'remaining' : 'upfront',
            'amount' => $amountDue,
            'status' => 'captured',
            'reference' => strtoupper(substr($data['payment_method'], 0, 3)) . '-' . $booking->id . '-' . now()->format('His'),
            'captured_at' => now(),
            'metadata' => [
                'booking_mode' => $booking->booking_mode,
                'payment_split_type' => $booking->payment_split_type,
            ],
        ]);

        if ($booking->payment_split_type === 'partial' && $booking->payment_status !== 'partial_paid' && (float) $booking->remaining_amount > 0) {
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
                'receipt_number' => $booking->receipt_number ?: $this->generateReceiptNumber($booking),
            ]);
        }

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function collectCash(Booking $booking): RedirectResponse
    {
        if ($booking->provider_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->payment_method !== 'cash') {
            return back()->with('error', 'This booking is not marked as cash on service.');
        }

        if ($booking->payment_status === 'paid') {
            return back()->with('error', 'Cash has already been collected.');
        }

        Payment::create([
            'booking_id' => $booking->id,
            'user_id' => $booking->taker_id,
            'method' => 'cash',
            'type' => 'cash_on_service',
            'amount' => (float) $booking->total,
            'status' => 'captured',
            'reference' => 'CASH-' . $booking->id . '-' . now()->format('His'),
            'captured_at' => now(),
        ]);

        $booking->update([
            'payment_status' => 'paid',
            'remaining_amount' => 0,
            'paid_at' => now(),
            'receipt_number' => $booking->receipt_number ?: $this->generateReceiptNumber($booking),
        ]);

        return back()->with('success', 'Cash payment marked as collected.');
    }

    public function tip(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->taker_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->status !== 'completed') {
            return back()->with('error', 'Tips are available after the service is completed.');
        }

        if (Payment::where('booking_id', $booking->id)
            ->where('user_id', Auth::id())
            ->where('type', 'tip')
            ->exists()) {
            return back()->with('error', 'You already tipped this provider for this booking.');
        }

        $data = $request->validate([
            'payment_method' => ['required', 'in:bkash,nagad,card,wallet'],
            'tip_amount' => ['required', 'numeric', 'min:1', 'max:50000'],
        ]);

        $tipAmount = round((float) $data['tip_amount'], 2);

        if ($data['payment_method'] === 'wallet') {
            $payerWallet = $this->walletFor(Auth::id());
            if ((float) $payerWallet->balance < $tipAmount) {
                return back()->with('error', 'Your wallet balance is not enough for this tip.');
            }

            $payerWallet->decrement('balance', $tipAmount);
            $this->recordWalletTransaction(
                $payerWallet,
                $booking,
                'debit',
                $tipAmount,
                'Tip paid to provider via wallet'
            );
        }

        Payment::create([
            'booking_id' => $booking->id,
            'user_id' => Auth::id(),
            'method' => $data['payment_method'],
            'type' => 'tip',
            'amount' => $tipAmount,
            'status' => 'captured',
            'reference' => 'TIP-' . $booking->id . '-' . now()->format('His'),
            'captured_at' => now(),
            'metadata' => [
                'provider_id' => $booking->provider_id,
            ],
        ]);

        $providerWallet = $this->walletFor((int) $booking->provider_id);
        $providerWallet->increment('balance', $tipAmount);

        WalletTransaction::create([
            'wallet_id' => $providerWallet->id,
            'user_id' => $booking->provider_id,
            'booking_id' => $booking->id,
            'type' => 'tip_credit',
            'amount' => $tipAmount,
            'balance_after' => (float) $providerWallet->fresh()->balance,
            'description' => 'Tip received from customer',
        ]);

        return back()->with('success', 'Tip sent successfully. Thank you for supporting your provider.');
    }

    public function requestRefund(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->taker_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
            'amount' => ['nullable', 'numeric', 'min:1'],
        ]);

        if (!in_array($booking->payment_status, ['paid', 'partial_paid'])) {
            return back()->with('error', 'Refunds are only available after a payment has been recorded.');
        }

        if ($booking->refundRequests()->where('status', 'pending')->exists()) {
            return back()->with('error', 'A refund request is already pending for this booking.');
        }

        RefundRequest::create([
            'booking_id' => $booking->id,
            'user_id' => Auth::id(),
            'amount' => $data['amount'] ?? ($booking->remaining_amount > 0 ? $booking->remaining_amount : $booking->total),
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Refund request submitted successfully.');
    }

    public function receipt(Booking $booking)
    {
        $user = Auth::user();
        if ($booking->taker_id !== $user->id && $booking->provider_id !== $user->id) {
            abort(403);
        }

        $booking->load(['service', 'provider', 'taker', 'payments']);

        $pdf = Pdf::loadView('receipts.booking', [
            'booking' => $booking,
            'payments' => $booking->payments,
            'receiptNumber' => $booking->receipt_number ?: $this->generateReceiptNumber($booking),
        ])->setPaper('a4');

        return $pdf->download('receipt-' . $booking->id . '.pdf');
    }

    private function walletFor(int $userId): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'cashback_balance' => 0]
        );
    }

    private function recordWalletTransaction(Wallet $wallet, Booking $booking, string $type, float $amount, string $description): void
    {
        $balanceAfter = (float) $wallet->fresh()->balance;

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'booking_id' => $booking->id,
            'type' => $type,
            'amount' => $amount,
            'balance_after' => $balanceAfter,
            'description' => $description,
        ]);
    }

    private function generateReceiptNumber(Booking $booking): string
    {
        return 'RCT-' . $booking->id . '-' . strtoupper(substr(md5((string) $booking->id . now()->timestamp), 0, 8));
    }
}
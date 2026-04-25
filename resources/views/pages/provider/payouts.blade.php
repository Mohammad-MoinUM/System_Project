@extends('layouts.app')

@section('content')
<section class="bg-base-200">
  <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Payout Management</h1>
    <p class="mt-2 text-base-content/60">Withdraw earnings to bKash, Nagad, or Bank and track history.</p>

    @if(session('success'))
      <div class="alert alert-success mt-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-error mt-4">{{ session('error') }}</div>
    @endif

    <div class="mt-6 grid gap-4 md:grid-cols-3">
      <div class="rounded-2xl bg-success/10 p-6">
        <p class="text-sm uppercase text-base-content/60">Wallet Balance</p>
        <p class="text-3xl font-black text-success">BDT {{ number_format((float) $wallet->balance, 2) }}</p>
        <p class="mt-2 text-sm text-base-content/60">Available to withdraw</p>
      </div>
      <div class="rounded-2xl bg-warning/10 p-6">
        <p class="text-sm uppercase text-base-content/60">Cash on Service</p>
        <p class="text-3xl font-black text-warning">BDT {{ number_format((float) ($cashOnServiceBalance ?? 0), 2) }}</p>
        <p class="mt-2 text-sm text-base-content/60">Pending cash job collections</p>
      </div>
      <div class="rounded-2xl bg-primary/10 p-6">
        <p class="text-sm uppercase text-base-content/60">Total Earnings</p>
        <p class="text-3xl font-black text-primary">BDT {{ number_format((float) ($totalEarnings ?? 0), 2) }}</p>
        <p class="mt-2 text-xs text-base-content/60">Current Balance + Withdrawn + Unpaid Cash</p>
      </div>
    </div>

    <div class="mt-8 rounded-2xl border border-base-300 bg-base-100 p-6">
      <h2 class="text-xl font-semibold">New Withdrawal Request</h2>
      <form method="POST" action="{{ route('provider.payouts.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
        @csrf
        <select name="payout_method" class="select select-bordered w-full" required>
          <option value="">Select payout method</option>
          <option value="bkash">bKash</option>
          <option value="nagad">Nagad</option>
          <option value="bank">Bank</option>
        </select>
        <input name="amount" type="number" step="0.01" min="1" max="{{ (float) $wallet->balance }}" class="input input-bordered w-full" placeholder="Amount" required>
        <input name="account_name" class="input input-bordered w-full" placeholder="Account holder name" required>
        <input name="account_number" class="input input-bordered w-full" placeholder="Account / mobile number" required>
        <input name="bank_name" class="input input-bordered w-full" placeholder="Bank name (bank only)">
        <input name="bank_branch" class="input input-bordered w-full" placeholder="Bank branch (optional)">
        <textarea name="notes" rows="3" class="textarea textarea-bordered md:col-span-2" placeholder="Additional notes"></textarea>
        <div class="md:col-span-2">
          <button type="submit" class="btn btn-primary btn-sm" @disabled((float) $wallet->balance <= 0)>Request Payout</button>
        </div>
      </form>
    </div>

    <div class="mt-8 overflow-x-auto rounded-2xl border border-base-300 bg-base-100">
      <div class="border-b border-base-300 px-6 py-4">
        <h2 class="text-xl font-semibold">Withdrawal Requests</h2>
      </div>
      <table class="table w-full">
        <thead>
          <tr>
            <th>Date</th>
            <th>Method</th>
            <th>Account</th>
            <th>Amount</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($requests as $request)
            <tr>
              <td>{{ $request->created_at->format('M j, Y g:i A') }}</td>
              <td class="uppercase">{{ $request->payout_method }}</td>
              <td>{{ $request->account_number }}</td>
              <td>BDT {{ number_format((float) $request->amount, 2) }}</td>
              <td>
                <span class="badge {{ $request->status === 'pending' ? 'badge-warning' : ($request->status === 'paid' ? 'badge-success' : 'badge-ghost') }}">{{ ucfirst($request->status) }}</span>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-base-content/50">No payout requests yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>

    <div class="mt-8 overflow-x-auto rounded-2xl border border-base-300 bg-base-100">
      <div class="border-b border-base-300 px-6 py-4">
        <h2 class="text-xl font-semibold">Transaction History</h2>
        <p class="mt-1 text-sm text-base-content/60">Tracks how money was received and debited in wallet.</p>
      </div>
      <table class="table w-full">
        <thead>
          <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Payment Method</th>
            <th>Amount</th>
            <th>Balance After</th>
            <th>Details</th>
          </tr>
        </thead>
        <tbody>
          @forelse($transactions as $tx)
            @php
              $isCredit = in_array($tx->type, ['booking_credit', 'cash_collection_credit', 'tip_credit', 'cashback'], true);
            @endphp
            <tr>
              <td>{{ $tx->created_at->format('M j, Y g:i A') }}</td>
              <td>
                <span class="badge {{ $isCredit ? 'badge-success' : 'badge-warning' }}">{{ str_replace('_', ' ', ucfirst($tx->type)) }}</span>
              </td>
              <td>{{ $tx->payment_method ? strtoupper($tx->payment_method) : 'N/A' }}</td>
              <td class="font-semibold {{ $isCredit ? 'text-success' : 'text-warning' }}">
                {{ $isCredit ? '+' : '-' }}BDT {{ number_format((float) $tx->amount, 2) }}
              </td>
              <td>BDT {{ number_format((float) $tx->balance_after, 2) }}</td>
              <td>{{ $tx->description ?: '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-base-content/50">No wallet transactions yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
  </div>
</section>
@endsection

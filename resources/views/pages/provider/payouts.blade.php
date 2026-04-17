@extends('layouts.app')

@section('content')
<section class="bg-base-200">
  <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Payout Management</h1>
    <p class="mt-2 text-base-content/60">Request withdrawal to bKash, Nagad, or Bank and track payout history.</p>

    @if(session('success'))
      <div class="alert alert-success mt-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-error mt-4">{{ session('error') }}</div>
    @endif

    <div class="mt-6 rounded-2xl bg-success/10 p-6">
      <p class="text-sm uppercase text-base-content/60">Available Balance</p>
      <p class="text-3xl font-black text-success">BDT {{ number_format((float) $wallet->balance, 2) }}</p>
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
        <input name="amount" type="number" step="0.01" min="1" class="input input-bordered w-full" placeholder="Amount" required>
        <input name="account_name" class="input input-bordered w-full" placeholder="Account holder name" required>
        <input name="account_number" class="input input-bordered w-full" placeholder="Account / mobile number" required>
        <input name="bank_name" class="input input-bordered w-full" placeholder="Bank name (bank only)">
        <input name="bank_branch" class="input input-bordered w-full" placeholder="Bank branch (optional)">
        <textarea name="notes" rows="3" class="textarea textarea-bordered md:col-span-2" placeholder="Additional notes"></textarea>
        <div class="md:col-span-2">
          <button type="submit" class="btn btn-primary btn-sm">Request Payout</button>
        </div>
      </form>
    </div>

    <div class="mt-8 overflow-x-auto rounded-2xl border border-base-300 bg-base-100">
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
                <span class="badge {{ $request->status === 'pending' ? 'badge-warning' : ($request->status === 'paid' ? 'badge-success' : 'badge-ghost') }}">
                  {{ ucfirst($request->status) }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-base-content/50">No payout requests yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>
  </div>
</section>
@endsection

@extends('layouts.app')

@section('content')

@php
    $currencyOptions = config('currencies.options', []);
    $currency = session('currency', config('currencies.default', 'BDT'));
    $currencyMeta = $currencyOptions[$currency] ?? ['symbol' => $currency, 'rate' => 1];
    $currencySymbol = $currencyMeta['symbol'] ?? $currency;
    $currencyRate = $currencyMeta['rate'] ?? 1;
@endphp

<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Earnings</h1>
    <p class="mt-2 text-base-content/60">Track your income from completed bookings.</p>
  </div>
</section>

{{-- Earnings Cards --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-2xl bg-primary/10 p-6">
        <h3 class="text-sm font-semibold text-base-content/60 uppercase">Today</h3>
        <p class="mt-2 text-3xl font-black text-base-content">{{ $currencySymbol }} {{ number_format($todayEarnings * $currencyRate, 2) }}</p>
      </div>
      <div class="rounded-2xl bg-primary/10 p-6">
        <h3 class="text-sm font-semibold text-base-content/60 uppercase">This Week</h3>
        <p class="mt-2 text-3xl font-black text-base-content">{{ $currencySymbol }} {{ number_format($weekEarnings * $currencyRate, 2) }}</p>
      </div>
      <div class="rounded-2xl bg-primary/10 p-6">
        <h3 class="text-sm font-semibold text-base-content/60 uppercase">This Month</h3>
        <p class="mt-2 text-3xl font-black text-base-content">{{ $currencySymbol }} {{ number_format($monthEarnings * $currencyRate, 2) }}</p>
      </div>
      <div class="rounded-2xl bg-success/10 p-6">
        <h3 class="text-sm font-semibold text-base-content/60 uppercase">All Time</h3>
        <p class="mt-2 text-3xl font-black text-success">{{ $currencySymbol }} {{ number_format($totalEarnings * $currencyRate, 2) }}</p>
      </div>
    </div>
  </div>
</section>

{{-- Recent Transactions --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-base-content">Recent Transactions</h2>
    <p class="mt-2 text-base-content/60">Your latest completed jobs.</p>

    <div class="mt-8 overflow-x-auto">
      <table class="table w-full">
        <thead>
          <tr class="bg-base-300">
            <th class="text-base font-bold">Service</th>
            <th class="text-base font-bold">Customer</th>
            <th class="text-base font-bold">Completed</th>
            <th class="text-base font-bold">Payment Method</th>
            <th class="text-base font-bold">Amount</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentTransactions as $tx)
            @php
              $latestPayment = $tx->payments
                ->sortByDesc(fn($payment) => $payment->captured_at ?? $payment->created_at)
                ->first();
              $paymentMethod = $tx->payment_method ?: ($latestPayment?->method ?? null);
            @endphp
            <tr class="hover">
              <td class="font-medium">{{ $tx->service->name ?? 'Service' }}</td>
              <td>{{ $tx->taker->name ?? 'N/A' }}</td>
              <td class="text-base-content/60">{{ $tx->updated_at->format('M j, g:i A') }}</td>
              <td>
                <span class="badge badge-ghost">{{ $paymentMethod ?? 'N/A' }}</span>
              </td>
              <td class="font-semibold text-success">{{ $currencySymbol }} {{ number_format($tx->total * $currencyRate, 2) }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-base-content/50">No completed transactions yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>

@endsection

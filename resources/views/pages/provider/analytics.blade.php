@extends('layouts.app')

@section('content')

@php
    $currencyOptions = config('currencies.options', []);
    $currency = session('currency', config('currencies.default', 'BDT'));
    $currencyMeta = $currencyOptions[$currency] ?? ['symbol' => $currency, 'rate' => 1];
    $currencySymbol = $currencyMeta['symbol'] ?? $currency;
    $currencyRate = $currencyMeta['rate'] ?? 1;

    $completionRate = $totalBookings > 0 ? round(($completedBookings / $totalBookings) * 100) : 0;
    $cancellationRate = $totalBookings > 0 ? round(($cancelledBookings / $totalBookings) * 100) : 0;
@endphp

<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Analytics</h1>
    <p class="mt-2 text-base-content/60">Insights into your business performance.</p>
  </div>
</section>

{{-- Key Metrics --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-base-content">Key Metrics</h2>
    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-2xl bg-primary/10 p-6">
        <h3 class="text-sm font-semibold text-base-content/60 uppercase">Total Bookings</h3>
        <p class="mt-2 text-3xl font-black text-base-content">{{ $totalBookings }}</p>
      </div>
      <div class="rounded-2xl bg-success/10 p-6">
        <h3 class="text-sm font-semibold text-base-content/60 uppercase">Completed</h3>
        <p class="mt-2 text-3xl font-black text-success">{{ $completedBookings }}</p>
        <p class="text-sm text-base-content/40">{{ $completionRate }}% rate</p>
      </div>
      <div class="rounded-2xl bg-warning/10 p-6">
        <h3 class="text-sm font-semibold text-base-content/60 uppercase">Avg. Rating</h3>
        <div class="mt-2 flex items-center gap-2">
          <x-heroicon-s-star class="w-6 h-6 text-warning" />
          <span class="text-3xl font-black text-base-content">{{ $avgRating ? number_format($avgRating, 1) : 'N/A' }}</span>
        </div>
        <p class="text-sm text-base-content/40">{{ $totalReviews }} {{ Str::plural('review', $totalReviews) }}</p>
      </div>
      <div class="rounded-2xl bg-info/10 p-6">
        <h3 class="text-sm font-semibold text-base-content/60 uppercase">Unique Clients</h3>
        <p class="mt-2 text-3xl font-black text-base-content">{{ $uniqueClients }}</p>
      </div>
    </div>
  </div>
</section>

{{-- Booking Performance --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-base-content">Booking Performance</h2>
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
      <div class="rounded-2xl bg-base-100 p-6 shadow-sm">
        <h3 class="text-lg font-bold text-base-content">Completion Rate</h3>
        <div class="mt-4 flex items-center gap-4">
          <div class="radial-progress text-success" style="--value:{{ $completionRate }}; --size:6rem; --thickness:0.5rem;" role="progressbar">{{ $completionRate }}%</div>
          <div>
            <p class="text-sm text-base-content/60">{{ $completedBookings }} completed out of {{ $totalBookings }}</p>
          </div>
        </div>
      </div>
      <div class="rounded-2xl bg-base-100 p-6 shadow-sm">
        <h3 class="text-lg font-bold text-base-content">Cancellation Rate</h3>
        <div class="mt-4 flex items-center gap-4">
          <div class="radial-progress text-error" style="--value:{{ $cancellationRate }}; --size:6rem; --thickness:0.5rem;" role="progressbar">{{ $cancellationRate }}%</div>
          <div>
            <p class="text-sm text-base-content/60">{{ $cancelledBookings }} cancelled out of {{ $totalBookings }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Monthly Earnings --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-base-content">Monthly Earnings (Last 6 Months)</h2>
    <div class="mt-6 overflow-x-auto">
      <table class="table w-full">
        <thead>
          <tr class="bg-base-200">
            <th class="text-base font-bold">Month</th>
            <th class="text-base font-bold">Earnings</th>
            <th class="text-base font-bold">Chart</th>
          </tr>
        </thead>
        <tbody>
          @php $maxEarning = collect($monthlyEarnings)->max('amount') ?: 1; @endphp
          @foreach($monthlyEarnings as $entry)
            <tr class="hover">
              <td class="font-medium">{{ $entry['month'] }}</td>
              <td class="font-semibold text-success">{{ $currencySymbol }} {{ number_format($entry['amount'] * $currencyRate, 2) }}</td>
              <td>
                <progress class="progress progress-success w-48 h-3" value="{{ $entry['amount'] }}" max="{{ $maxEarning }}"></progress>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>

@endsection

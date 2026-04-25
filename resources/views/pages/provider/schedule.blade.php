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
    <h1 class="text-3xl font-bold text-base-content">Schedule</h1>
    <p class="mt-2 text-base-content/60">Your upcoming bookings and appointments.</p>
  </div>
</section>

<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    @if(session('success'))
      <div class="alert alert-success mb-6">{{ session('success') }}</div>
    @endif

    <div class="space-y-4">
      @forelse($upcomingBookings as $booking)
        @php
          $statusClass = match($booking->status) {
            'pending'     => 'badge-warning',
            'active'      => 'badge-info',
            'in_progress' => 'badge-info',
            'awaiting_confirmation' => 'badge-warning',
            default       => 'badge-ghost',
          };
          $isPast = $booking->scheduled_at && $booking->scheduled_at->isPast();
        @endphp

        <div class="card card-side bg-base-100 border border-base-200 shadow-sm {{ $isPast ? 'opacity-60' : '' }}">
          <div class="card-body p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <div class="flex items-center gap-2">
                  @if($booking->scheduled_at)
                    <div class="text-center rounded-lg bg-primary/10 px-3 py-2">
                      <p class="text-xs font-semibold text-primary uppercase">{{ $booking->scheduled_at->format('M') }}</p>
                      <p class="text-2xl font-black text-primary">{{ $booking->scheduled_at->format('d') }}</p>
                    </div>
                  @endif
                  <div>
                    <h3 class="text-lg font-bold text-base-content">{{ $booking->service->name ?? 'Service' }}</h3>
                    <p class="text-sm text-base-content/60">
                      {{ $booking->taker->name ?? 'Customer' }}
                      @if($booking->taker->phone) &middot; {{ $booking->taker->phone }} @endif
                    </p>
                    @if($booking->scheduled_at)
                      <p class="text-xs text-base-content/40 mt-1">
                        {{ $booking->scheduled_at->format('l, g:i A') }}
                        @if($isPast) <span class="text-error">(overdue)</span> @endif
                      </p>
                    @endif
                  </div>
                </div>
              </div>
              <div class="flex flex-col items-end gap-2">
                <span class="badge {{ $statusClass }} badge-outline uppercase text-xs font-semibold">
                  {{ str_replace('_', ' ', $booking->status) }}
                </span>
                <span class="text-lg font-bold text-primary">{{ $currencySymbol }} {{ number_format($booking->total * $currencyRate, 2) }}</span>
              </div>
            </div>

            <div class="mt-3 flex flex-wrap gap-2 border-t border-base-200 pt-3">
              <a href="{{ route('booking.show', $booking) }}" class="btn btn-ghost btn-xs">View</a>
              @if($booking->status === 'pending')
                <form method="POST" action="{{ route('booking.accept', $booking) }}">@csrf<button type="submit" class="btn btn-primary btn-xs">Accept</button></form>
                <form method="POST" action="{{ route('booking.reject', $booking) }}">@csrf<button type="submit" class="btn btn-error btn-outline btn-xs">Reject</button></form>
              @endif
              @if($booking->status === 'active')
                <form method="POST" action="{{ route('booking.start', $booking) }}">@csrf<button type="submit" class="btn btn-info btn-xs">Start</button></form>
              @endif
              @if(in_array($booking->status, ['active', 'in_progress']))
                <form method="POST" action="{{ route('booking.complete', $booking) }}">@csrf<button type="submit" class="btn btn-success btn-xs">{{ $booking->payment_method === 'cash' ? 'Complete' : 'Request Confirmation' }}</button></form>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="text-center py-16">
          <x-heroicon-o-calendar-days class="w-12 h-12 text-base-content/20 mx-auto" />
          <p class="mt-4 text-lg text-base-content/50">No upcoming appointments.</p>
          <p class="text-sm text-base-content/40 mt-1">New bookings will appear here when customers book your services.</p>
        </div>
      @endforelse
    </div>
  </div>
</section>

@endsection

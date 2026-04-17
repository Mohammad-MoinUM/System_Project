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
    <h1 class="text-3xl font-bold text-base-content">My Bookings</h1>
    <p class="mt-2 text-base-content/60">View and manage all your bookings.</p>
  </div>
</section>

<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    @if(session('success'))
      <div class="alert alert-success mb-6">{{ session('success') }}</div>
    @endif

    <div class="overflow-x-auto">
      <table class="table w-full">
        <thead>
          <tr class="bg-base-200">
            <th class="text-base font-bold">Service</th>
            <th class="text-base font-bold">Provider</th>
            <th class="text-base font-bold">Scheduled</th>
            <th class="text-base font-bold">Total</th>
            <th class="text-base font-bold">Status</th>
            <th class="text-base font-bold">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($bookings as $booking)
            @php
              $statusClass = match($booking->status) {
                'pending'     => 'badge-warning',
                'active'      => 'badge-info',
                'in_progress' => 'badge-info',
                'completed'   => 'badge-success',
                'cancelled'   => 'badge-error',
                default       => 'badge-ghost',
              };
              $hasReview = $booking->reviews->where('taker_id', auth()->id())->isNotEmpty();
            @endphp
            <tr class="hover">
              <td class="font-medium">{{ $booking->service->name ?? 'N/A' }}</td>
              <td>{{ $booking->provider->name ?? 'N/A' }}</td>
              <td class="text-base-content/60">{{ $booking->scheduled_at ? $booking->scheduled_at->format('M j, g:i A') : 'Not set' }}</td>
              <td class="font-semibold">{{ $currencySymbol }} {{ number_format($booking->total * $currencyRate, 2) }}</td>
              <td>
                <span class="badge {{ $statusClass }} badge-outline uppercase text-xs font-semibold">
                  {{ str_replace('_', ' ', $booking->status) }}
                </span>
              </td>
              <td>
                <div class="flex gap-2">
                  <a href="{{ route('booking.show', $booking) }}" class="btn btn-ghost btn-xs">View</a>
                  @if(in_array($booking->status, ['completed', 'cancelled']))
                    <a href="{{ route('booking.rebook', $booking) }}" class="btn btn-outline btn-primary btn-xs">Rebook</a>
                  @endif
                  @if($booking->status === 'completed' && !$hasReview)
                    <a href="{{ route('booking.show', $booking) }}#review" class="btn btn-primary btn-xs">Review</a>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-base-content/50 py-8">No bookings yet. <a href="{{ route('customer.browse') }}" class="text-primary hover:underline">Browse services</a></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-6">
      {{ $bookings->links() }}
    </div>
  </div>
</section>

@endsection

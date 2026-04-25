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
    <h1 class="text-3xl font-bold text-base-content">All Jobs</h1>
    <p class="mt-2 text-base-content/60">Manage all your booking requests.</p>

    {{-- Status counts --}}
    <div class="mt-6 flex flex-wrap gap-3">
      <span class="badge badge-warning badge-lg">Pending: {{ $counts['pending'] }}</span>
      <span class="badge badge-info badge-lg">Active: {{ $counts['active'] }}</span>
      <span class="badge badge-info badge-outline badge-lg">In Progress: {{ $counts['in_progress'] }}</span>
      <span class="badge badge-warning badge-outline badge-lg">Awaiting Confirmation: {{ $counts['awaiting_confirmation'] }}</span>
      <span class="badge badge-success badge-lg">Completed: {{ $counts['completed'] }}</span>
      <span class="badge badge-error badge-outline badge-lg">Cancelled: {{ $counts['cancelled'] }}</span>
    </div>
  </div>
</section>

<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    @if(session('success'))
      <div class="alert alert-success mb-6">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-error mb-6">{{ session('error') }}</div>
    @endif

    <div class="space-y-4">
      @forelse($bookings as $booking)
        @php
          $statusClass = match($booking->status) {
            'pending'     => 'badge-warning',
            'active'      => 'badge-info',
            'in_progress' => 'badge-info',
            'awaiting_confirmation' => 'badge-warning',
            'completed'   => 'badge-success',
            'cancelled'   => 'badge-error',
            default       => 'badge-ghost',
          };
        @endphp

        <div class="card card-side bg-base-100 border border-base-200 shadow-sm">
          <div class="card-body p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <h3 class="text-lg font-bold text-base-content">{{ $booking->service->name ?? 'Service' }}</h3>
                <p class="text-sm text-base-content/60">
                  Customer: {{ $booking->taker->name ?? 'N/A' }}
                  @if($booking->taker->phone)
                    &middot; {{ $booking->taker->phone }}
                  @endif
                </p>
                <p class="text-xs text-base-content/40 mt-1">
                  Scheduled: {{ $booking->scheduled_at ? $booking->scheduled_at->format('M j, Y \a\t g:i A') : 'Not set' }}
                  &middot; Booked {{ $booking->created_at->diffForHumans() }}
                </p>
                @if($booking->notes)
                  <p class="text-sm text-base-content/50 mt-2 italic">"{{ Str::limit($booking->notes, 100) }}"</p>
                @endif
              </div>
              <div class="text-right flex flex-col items-end gap-2">
                <span class="badge {{ $statusClass }} badge-outline uppercase text-xs font-semibold">
                  {{ str_replace('_', ' ', $booking->status) }}
                </span>
                <span class="text-lg font-bold text-primary">{{ $currencySymbol }} {{ number_format($booking->total * $currencyRate, 2) }}</span>
              </div>
            </div>

            {{-- Actions --}}
            <div class="mt-4 flex flex-wrap gap-2 border-t border-base-200 pt-3">
              <a href="{{ route('booking.show', $booking) }}" class="btn btn-ghost btn-xs">View Details</a>

              @if($booking->status === 'pending')
                <form method="POST" action="{{ route('booking.accept', $booking) }}">
                  @csrf
                  <button type="submit" class="btn btn-primary btn-xs">Accept</button>
                </form>
                <form method="POST" action="{{ route('booking.reject', $booking) }}">
                  @csrf
                  <button type="submit" class="btn btn-error btn-outline btn-xs">Reject</button>
                </form>
              @endif

              @if($booking->status === 'active')
                <form method="POST" action="{{ route('booking.start', $booking) }}">
                  @csrf
                  <button type="submit" class="btn btn-info btn-xs">Start Job</button>
                </form>
              @endif

              @if(in_array($booking->status, ['active', 'in_progress']))
                <form method="POST" action="{{ route('booking.complete', $booking) }}">
                  @csrf
                  <button type="submit" class="btn btn-success btn-xs">{{ $booking->payment_method === 'cash' ? 'Complete' : 'Request Confirmation' }}</button>
                </form>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="text-center py-16">
          <x-heroicon-o-clipboard-document class="w-12 h-12 text-base-content/20 mx-auto" />
          <p class="mt-4 text-lg text-base-content/50">No jobs yet.</p>
        </div>
      @endforelse
    </div>

    <div class="mt-8">
      {{ $bookings->links() }}
    </div>
  </div>
</section>

@endsection

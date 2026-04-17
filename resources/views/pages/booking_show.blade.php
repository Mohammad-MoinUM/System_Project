@extends('layouts.app')

@section('content')

@php
    $currencyOptions = config('currencies.options', []);
    $currency = session('currency', config('currencies.default', 'BDT'));
    $currencyMeta = $currencyOptions[$currency] ?? ['symbol' => $currency, 'rate' => 1];
    $currencySymbol = $currencyMeta['symbol'] ?? $currency;
    $currencyRate = $currencyMeta['rate'] ?? 1;

    $user = auth()->user();
    $isProvider = $booking->provider_id === $user->id;
    $isCustomer = $booking->taker_id === $user->id;

    $statusColors = [
        'pending'     => 'badge-warning',
        'active'      => 'badge-info',
        'in_progress' => 'badge-info',
        'completed'   => 'badge-success',
        'cancelled'   => 'badge-error',
    ];
@endphp

<section class="bg-base-200">
  <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">

    @if(session('success'))
      <div class="alert alert-success mb-6">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-error mb-6">{{ session('error') }}</div>
    @endif

    <a href="{{ url()->previous() }}" class="inline-flex items-center gap-1.5 text-sm text-base-content/60 hover:text-base-content mb-6 transition-colors">
      <x-heroicon-o-arrow-left class="w-4 h-4" />
      Back
    </a>

    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-base-content">Booking #{{ $booking->id }}</h1>
      <span class="badge {{ $statusColors[$booking->status] ?? 'badge-ghost' }} badge-lg uppercase font-semibold">
        {{ str_replace('_', ' ', $booking->status) }}
      </span>
    </div>

    {{-- Service & Provider Info --}}
    <div class="mt-8 rounded-2xl border border-base-200 bg-base-100 p-6">
      <h2 class="text-lg font-bold text-base-content mb-4">Service Details</h2>
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <p class="text-xs text-base-content/40 uppercase font-semibold">Service</p>
          <p class="text-base-content font-medium">{{ $booking->service->name ?? 'N/A' }}</p>
        </div>
        <div>
          <p class="text-xs text-base-content/40 uppercase font-semibold">Category</p>
          <p class="text-base-content">{{ $booking->service->category ?? 'N/A' }}</p>
        </div>
        <div>
          <p class="text-xs text-base-content/40 uppercase font-semibold">Provider</p>
          <p class="text-base-content font-medium">{{ $booking->provider->name ?? 'N/A' }}</p>
        </div>
        <div>
          <p class="text-xs text-base-content/40 uppercase font-semibold">Customer</p>
          <p class="text-base-content font-medium">{{ $booking->taker->name ?? 'N/A' }}</p>
        </div>
        <div>
          <p class="text-xs text-base-content/40 uppercase font-semibold">Scheduled At</p>
          <p class="text-base-content">{{ $booking->scheduled_at ? $booking->scheduled_at->format('D, M j, Y \a\t g:i A') : 'Not scheduled' }}</p>
        </div>
        <div>
          <p class="text-xs text-base-content/40 uppercase font-semibold">Total</p>
          <p class="text-xl font-bold text-primary">{{ $currencySymbol }} {{ number_format($booking->total * $currencyRate, 2) }}</p>
        </div>
      </div>

      @if($booking->notes)
      <div class="mt-4 border-t border-base-200 pt-4">
        <p class="text-xs text-base-content/40 uppercase font-semibold">Notes</p>
        <p class="text-base-content/70 mt-1">{{ $booking->notes }}</p>
      </div>
      @endif

      <div class="mt-4 border-t border-base-200 pt-4 text-xs text-base-content/40">
        Created {{ $booking->created_at->diffForHumans() }}
      </div>
    </div>

    {{-- Action Buttons --}}
    <div class="mt-6 flex flex-wrap gap-3">
      <a href="{{ route('booking.chat', $booking) }}" class="btn btn-outline btn-sm">Open Chat</a>

      @if($isProvider)
        @if($booking->status === 'pending')
          <form method="POST" action="{{ route('booking.accept', $booking) }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">Accept Booking</button>
          </form>
          <form method="POST" action="{{ route('booking.reject', $booking) }}">
            @csrf
            <button type="submit" class="btn btn-error btn-outline btn-sm">Reject</button>
          </form>
        @endif

        @if($booking->status === 'active')
          <form method="POST" action="{{ route('booking.start', $booking) }}">
            @csrf
            <button type="submit" class="btn btn-info btn-sm">Start Job</button>
          </form>
        @endif

        @if(in_array($booking->status, ['active', 'in_progress']))
          <form method="POST" action="{{ route('booking.complete', $booking) }}">
            @csrf
            <button type="submit" class="btn btn-success btn-sm">Mark Completed</button>
          </form>
        @endif
      @endif

      @if($isCustomer && !in_array($booking->status, ['completed', 'cancelled']))
        <form method="POST" action="{{ route('booking.cancel', $booking) }}">
          @csrf
          <button type="submit" class="btn btn-error btn-outline btn-sm">Cancel Booking</button>
        </form>
      @endif
    </div>

    {{-- Review Section (for customer, on completed bookings) --}}
    @if($isCustomer && $booking->status === 'completed')
      <div class="mt-8 rounded-2xl border border-base-200 bg-base-100 p-6">
        @php $existingReview = $booking->reviews->where('taker_id', $user->id)->first(); @endphp

        @if($existingReview)
          <h2 class="text-lg font-bold text-base-content">Your Review</h2>
          <div class="mt-4 flex items-center gap-1">
            @for($i = 1; $i <= 5; $i++)
              <x-heroicon-s-star class="h-5 w-5 {{ $i <= $existingReview->rating ? 'text-warning' : 'text-base-300' }}" />
            @endfor
            <span class="ml-2 font-semibold">{{ $existingReview->rating }}.0</span>
          </div>
          @if($existingReview->comment)
            <p class="mt-2 text-base-content/70 italic">"{{ $existingReview->comment }}"</p>
          @endif
        @else
          <h2 class="text-lg font-bold text-base-content">Leave a Review</h2>
          <form method="POST" action="{{ route('review.store') }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="booking_id" value="{{ $booking->id }}" />

            <div>
              <label class="label"><span class="label-text font-semibold">Rating</span></label>
              <div class="rating rating-lg">
                @for($i = 1; $i <= 5; $i++)
                  <input type="radio" name="rating" value="{{ $i }}" class="mask mask-star-2 bg-warning"
                    {{ old('rating') == $i ? 'checked' : '' }} {{ $i === 5 && !old('rating') ? 'checked' : '' }} />
                @endfor
              </div>
              @error('rating') <span class="text-error text-sm block">{{ $message }}</span> @enderror
            </div>

            <div>
              <label class="label"><span class="label-text font-semibold">Comment (optional)</span></label>
              <textarea name="comment" rows="3" class="textarea textarea-bordered w-full" placeholder="How was the service?">{{ old('comment') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
          </form>
        @endif
      </div>
    @endif

  </div>
</section>

@endsection

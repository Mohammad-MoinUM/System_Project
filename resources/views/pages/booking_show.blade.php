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
    $myTip = $booking->payments->first(function ($payment) use ($user) {
      return (int) $payment->user_id === (int) $user->id && $payment->type === 'tip';
    });
    $myComplaint = $booking->complaints->first(function ($complaint) use ($user) {
      return (int) $complaint->user_id === (int) $user->id;
    });

    $statusColors = [
        'pending'     => 'badge-warning',
        'active'      => 'badge-info',
        'in_progress' => 'badge-info',
        'completed'   => 'badge-success',
        'cancelled'   => 'badge-error',
    ];

    $modeLabels = [
      'instant' => 'Book now',
      'scheduled' => 'Scheduled',
    ];

    $recurrenceLabels = [
      'weekly' => 'Weekly',
      'monthly' => 'Monthly',
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

    <div class="mt-6 rounded-2xl border border-base-200 bg-base-100 p-6">
      <h2 class="text-lg font-bold text-base-content mb-4">Booking Timeline</h2>
      @php
        $timelineSteps = [
          ['label' => 'Booked', 'done' => true, 'time' => $booking->created_at],
          ['label' => 'Accepted', 'done' => in_array($booking->status, ['active', 'in_progress', 'completed']), 'time' => $booking->status !== 'pending' ? $booking->updated_at : null],
          ['label' => 'In Progress', 'done' => in_array($booking->status, ['in_progress', 'completed']), 'time' => in_array($booking->status, ['in_progress', 'completed']) ? $booking->updated_at : null],
          ['label' => 'Completed', 'done' => $booking->status === 'completed', 'time' => $booking->status === 'completed' ? $booking->updated_at : null],
          ['label' => 'Cancelled', 'done' => $booking->status === 'cancelled', 'time' => $booking->status === 'cancelled' ? $booking->cancelled_at : null],
        ];
      @endphp
      <div class="grid gap-3 sm:grid-cols-5">
        @foreach($timelineSteps as $step)
          <div class="rounded-xl border {{ $step['done'] ? 'border-primary/30 bg-primary/5' : 'border-base-200 bg-base-200/50' }} p-4">
            <div class="flex items-center gap-2">
              <span class="inline-flex h-3 w-3 rounded-full {{ $step['done'] ? 'bg-primary' : 'bg-base-300' }}"></span>
              <p class="font-semibold text-base-content">{{ $step['label'] }}</p>
            </div>
            <p class="mt-2 text-xs text-base-content/50">{{ $step['time'] ? $step['time']->diffForHumans() : 'Waiting' }}</p>
          </div>
        @endforeach
      </div>
    </div>

    <div class="mt-8 rounded-2xl border border-base-200 bg-base-100 p-6">
      <h2 class="text-lg font-bold text-base-content mb-4">Booking Summary</h2>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
          <p class="text-xs text-base-content/40 uppercase font-semibold">Booking Type</p>
          <p class="text-base-content font-medium">{{ $modeLabels[$booking->booking_mode] ?? ucfirst($booking->booking_mode ?? 'scheduled') }}</p>
        </div>
        <div>
          <p class="text-xs text-base-content/40 uppercase font-semibold">Recurring</p>
          <p class="text-base-content font-medium">
            @if($booking->recurrence_type)
              {{ $recurrenceLabels[$booking->recurrence_type] ?? ucfirst($booking->recurrence_type) }} every {{ $booking->recurrence_interval ?? 1 }}
              @if($booking->recurrence_end_date)
                until {{ $booking->recurrence_end_date->format('M d, Y') }}
              @endif
            @else
              One-time
            @endif
          </p>
        </div>
        <div>
          <p class="text-xs text-base-content/40 uppercase font-semibold">Estimated Arrival</p>
          <p class="text-base-content font-medium" id="tracking-eta">{{ $booking->estimated_arrival_at ? $booking->estimated_arrival_at->format('M d, Y g:i A') : 'Pending provider confirmation' }}</p>
        </div>
        <div>
          <p class="text-xs text-base-content/40 uppercase font-semibold">Payment Status</p>
          <p class="text-base-content font-medium">{{ str_replace('_', ' ', ucfirst($booking->payment_status ?? 'unpaid')) }}</p>
        </div>
        <div>
          <p class="text-xs text-base-content/40 uppercase font-semibold">Payment Method</p>
          <p class="text-base-content font-medium">{{ ucfirst($booking->payment_method ?? 'n/a') }}</p>
        </div>
        <div>
          <p class="text-xs text-base-content/40 uppercase font-semibold">Cashback</p>
          <p class="text-base-content font-medium">{{ $currencySymbol }} {{ number_format((float) ($booking->cashback_amount ?? 0) * $currencyRate, 2) }}</p>
        </div>
      </div>
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
        @if($booking->service_address_label || $booking->service_address_line1)
          <div class="sm:col-span-2 lg:col-span-3">
            <p class="text-xs text-base-content/40 uppercase font-semibold">Service Address</p>
            <p class="text-base-content font-medium">{{ $booking->service_address_label ?: 'Service location' }}</p>
            <p class="text-sm text-base-content/70 mt-1">{{ $booking->service_address_line1 }}</p>
            @if($booking->service_address_line2)
              <p class="text-sm text-base-content/70">{{ $booking->service_address_line2 }}</p>
            @endif
            <p class="text-sm text-base-content/60 mt-1">{{ collect([$booking->service_area, $booking->service_city, $booking->service_postal_code])->filter()->implode(', ') }}</p>
          </div>
        @endif
      </div>

      @if(isset($extraServices) && $extraServices->isNotEmpty())
        <div class="mt-4 border-t border-base-200 pt-4">
          <p class="text-xs text-base-content/40 uppercase font-semibold mb-2">Add-on Services</p>
          <div class="flex flex-wrap gap-2">
            @foreach($extraServices as $extraService)
              <span class="badge badge-outline">{{ $extraService->name }}</span>
            @endforeach
          </div>
        </div>
      @endif

      @if($booking->notes)
      <div class="mt-4 border-t border-base-200 pt-4">
        <p class="text-xs text-base-content/40 uppercase font-semibold">Notes</p>
        <p class="text-base-content/70 mt-1">{{ $booking->notes }}</p>
      </div>
      @endif

      @if($booking->status === 'cancelled' && $booking->cancellation_reason)
      <div class="mt-4 border-t border-base-200 pt-4">
        <p class="text-xs text-base-content/40 uppercase font-semibold">Cancellation Reason</p>
        <p class="text-base-content/70 mt-1">{{ $booking->cancellation_reason }}</p>
        @if($booking->cancelled_at)
          <p class="mt-2 text-xs text-base-content/40">Cancelled {{ $booking->cancelled_at->diffForHumans() }}</p>
        @endif
      </div>
      @endif

      @if(isset($attachments) && $attachments->isNotEmpty())
        <div class="mt-4 border-t border-base-200 pt-4">
          <p class="text-xs text-base-content/40 uppercase font-semibold mb-3">Uploads</p>
          <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($attachments as $attachment)
              <a href="{{ $attachment['url'] }}" target="_blank" class="rounded-xl border border-base-200 p-3 hover:border-primary transition-colors">
                <p class="font-medium text-base-content text-sm truncate">{{ $attachment['name'] }}</p>
                <p class="text-xs text-base-content/50 mt-1">{{ $attachment['type'] }}</p>
              </a>
            @endforeach
          </div>
        </div>
      @endif

      <div class="mt-4 border-t border-base-200 pt-4 text-xs text-base-content/40">
        Created {{ $booking->created_at->diffForHumans() }}
      </div>
    </div>

    <div class="mt-8 rounded-2xl border border-base-200 bg-base-100 p-6">
      <div class="flex items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-base-content">Live Tracking</h2>
        <span class="badge badge-outline">{{ ucfirst(str_replace('_', ' ', $booking->tracking_status ?? 'not_started')) }}</span>
      </div>

      <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl bg-base-200/60 p-4">
          <p class="text-xs text-base-content/40 uppercase font-semibold">Estimated Arrival</p>
          <p class="mt-1 text-xl font-bold text-primary tracking-tight" id="tracking-eta-value">{{ $booking->estimated_arrival_at ? $booking->estimated_arrival_at->format('M d, Y g:i A') : 'Waiting for provider' }}</p>
          <p class="mt-2 text-sm text-base-content/60" id="tracking-updated">{{ $booking->tracking_updated_at ? 'Updated ' . $booking->tracking_updated_at->diffForHumans() : 'No tracking update yet' }}</p>
        </div>
        <div class="rounded-xl bg-base-200/60 p-4">
          <p class="text-xs text-base-content/40 uppercase font-semibold">Location</p>
          <p class="mt-1 text-base-content" id="tracking-location">
            @if($booking->provider_latitude && $booking->provider_longitude)
              {{ $booking->provider_latitude }}, {{ $booking->provider_longitude }}
            @else
              Provider location not shared yet
            @endif
          </p>
        </div>
      </div>

      @if($isProvider)
        <form method="POST" action="{{ route('booking.tracking.update', $booking) }}" class="mt-6 grid gap-4 sm:grid-cols-2">
          @csrf
          <div>
            <label class="label"><span class="label-text font-semibold">Tracking status</span></label>
            <select name="tracking_status" class="select select-bordered w-full">
              <option value="not_started" @selected($booking->tracking_status === 'not_started')>Not started</option>
              <option value="en_route" @selected($booking->tracking_status === 'en_route')>En route</option>
              <option value="arrived" @selected($booking->tracking_status === 'arrived')>Arrived</option>
            </select>
          </div>
          <div>
            <label class="label"><span class="label-text font-semibold">ETA minutes</span></label>
            <input type="number" name="eta_minutes" min="1" max="720" class="input input-bordered w-full" placeholder="45" />
          </div>
          <div>
            <label class="label"><span class="label-text font-semibold">Latitude</span></label>
            <input type="text" name="provider_latitude" class="input input-bordered w-full" placeholder="23.8103" />
          </div>
          <div>
            <label class="label"><span class="label-text font-semibold">Longitude</span></label>
            <input type="text" name="provider_longitude" class="input input-bordered w-full" placeholder="90.4125" />
          </div>
          <div class="sm:col-span-2 flex flex-wrap gap-3">
            <button type="button" class="btn btn-outline btn-sm" onclick="useMyLocation()">Use my current location</button>
            <button type="submit" class="btn btn-primary btn-sm">Update tracking</button>
          </div>
        </form>
      @else
        <p class="mt-4 text-sm text-base-content/60">This section refreshes automatically while the provider updates their route.</p>
      @endif
    </div>

    <div class="mt-8 rounded-2xl border border-base-200 bg-base-100 p-6">
      <div class="flex items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-base-content">Payments</h2>
        <a href="{{ route('booking.receipt', $booking) }}" class="btn btn-outline btn-sm">Download Receipt PDF</a>
      </div>

      <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl bg-base-200/60 p-4">
          <p class="text-xs text-base-content/40 uppercase font-semibold">Upfront</p>
          <p class="mt-1 text-lg font-bold">{{ $currencySymbol }} {{ number_format((float) $booking->upfront_amount * $currencyRate, 2) }}</p>
          <p class="text-sm text-base-content/60">Remaining: {{ $currencySymbol }} {{ number_format((float) $booking->remaining_amount * $currencyRate, 2) }}</p>
        </div>
        <div class="rounded-xl bg-base-200/60 p-4">
          <p class="text-xs text-base-content/40 uppercase font-semibold">Cash on service</p>
          <p class="mt-1 text-sm text-base-content/70">Collected by the provider after the visit when you select cash.</p>
        </div>
      </div>

      @if($isCustomer && in_array($booking->payment_status, ['unpaid', 'partial_paid']) && $booking->payment_method !== 'cash')
        <form method="POST" action="{{ route('booking.pay', $booking) }}" class="mt-6 grid gap-4 sm:grid-cols-2">
          @csrf
          <div>
            <label class="label"><span class="label-text font-semibold">Pay with</span></label>
            <select name="payment_method" class="select select-bordered w-full">
              <option value="bkash" @selected($booking->payment_method === 'bkash')>bKash</option>
              <option value="nagad" @selected($booking->payment_method === 'nagad')>Nagad</option>
              <option value="card" @selected($booking->payment_method === 'card')>Card</option>
              <option value="wallet" @selected($booking->payment_method === 'wallet')>Wallet / Credits</option>
            </select>
          </div>
          <div class="flex items-end">
            <button type="submit" class="btn btn-primary w-full">{{ $booking->payment_status === 'partial_paid' ? 'Pay Remaining' : 'Pay Upfront' }}</button>
          </div>
        </form>
      @endif

      @if($isProvider && $booking->payment_method === 'cash' && $booking->payment_status !== 'paid')
        <form method="POST" action="{{ route('booking.cash-collected', $booking) }}" class="mt-6">
          @csrf
          <button type="submit" class="btn btn-success btn-sm">Mark Cash as Collected</button>
        </form>
      @endif

      @if($isCustomer && in_array($booking->payment_status, ['paid', 'partial_paid']))
        <div class="mt-6 rounded-xl border border-base-200 p-4">
          <h3 class="font-semibold text-base-content mb-3">Request Refund</h3>
          <form method="POST" action="{{ route('booking.refund', $booking) }}" class="space-y-3">
            @csrf
            <div>
              <label class="label"><span class="label-text font-semibold">Refund reason</span></label>
              <textarea name="reason" rows="3" class="textarea textarea-bordered w-full" placeholder="Tell us why you need a refund..." required></textarea>
            </div>
            <div>
              <label class="label"><span class="label-text font-semibold">Refund amount (optional)</span></label>
              <input type="number" name="amount" min="1" step="0.01" class="input input-bordered w-full" placeholder="Leave blank to request the eligible amount" />
            </div>
            <button type="submit" class="btn btn-outline btn-error btn-sm">Submit Refund Request</button>
          </form>
        </div>
      @endif

      @if($isCustomer && $booking->status === 'completed')
        <div class="mt-6 rounded-xl border border-base-200 p-4">
          <h3 class="font-semibold text-base-content mb-3">Tip the Provider</h3>

          @if($myTip)
            <p class="text-sm text-base-content/70">
              You tipped <span class="font-semibold">{{ $currencySymbol }} {{ number_format((float) $myTip->amount * $currencyRate, 2) }}</span>
              via {{ ucfirst($myTip->method) }}.
            </p>
          @else
            <form method="POST" action="{{ route('booking.tip', $booking) }}" class="grid gap-3 sm:grid-cols-2">
              @csrf
              <div>
                <label class="label"><span class="label-text font-semibold">Tip amount</span></label>
                <input type="number" name="tip_amount" min="1" step="0.01" class="input input-bordered w-full" placeholder="100" value="{{ old('tip_amount') }}" required />
                @error('tip_amount') <span class="text-error text-sm block mt-1">{{ $message }}</span> @enderror
              </div>
              <div>
                <label class="label"><span class="label-text font-semibold">Pay with</span></label>
                <select name="payment_method" class="select select-bordered w-full" required>
                  <option value="bkash" @selected(old('payment_method', 'bkash') === 'bkash')>bKash</option>
                  <option value="nagad" @selected(old('payment_method') === 'nagad')>Nagad</option>
                  <option value="card" @selected(old('payment_method') === 'card')>Card</option>
                  <option value="wallet" @selected(old('payment_method') === 'wallet')>Wallet / Credits</option>
                </select>
                @error('payment_method') <span class="text-error text-sm block mt-1">{{ $message }}</span> @enderror
              </div>
              <div class="sm:col-span-2">
                <button type="submit" class="btn btn-primary btn-sm">Send Tip</button>
              </div>
            </form>
          @endif
        </div>
      @endif
    </div>

    {{-- Action Buttons --}}
    <div class="mt-6 flex flex-wrap gap-3">
      <a href="{{ route('booking.chat', $booking) }}" class="btn btn-outline btn-sm">Open Booking Chat</a>

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
        <form method="POST" action="{{ route('booking.cancel', $booking) }}" class="w-full max-w-xl space-y-3 rounded-2xl border border-base-200 bg-base-100 p-4">
          @csrf
          <div>
            <label class="label"><span class="label-text font-semibold">Reason for cancellation</span></label>
            <textarea name="cancellation_reason" rows="3" class="textarea textarea-bordered w-full" placeholder="Tell us why you need to cancel this booking..." required>{{ old('cancellation_reason') }}</textarea>
            @error('cancellation_reason') <span class="text-error text-sm block mt-1">{{ $message }}</span> @enderror
          </div>
          <button type="submit" class="btn btn-error btn-outline btn-sm">Cancel Booking</button>
        </form>
      @endif

      @if($isCustomer && in_array($booking->status, ['completed', 'cancelled']))
        <a href="{{ route('booking.rebook', $booking) }}" class="btn btn-primary btn-sm">Rebook This Service</a>
        <form method="POST" action="{{ route('booking.rebook.now', $booking) }}">
          @csrf
          <button type="submit" class="btn btn-secondary btn-sm">One-Click Rebook Same Provider</button>
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

          <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-base-200/60 p-3">
              <p class="text-xs text-base-content/50 uppercase font-semibold">Punctuality</p>
              <p class="mt-1 font-bold">{{ $existingReview->punctuality_rating ?? $existingReview->rating }}/5</p>
            </div>
            <div class="rounded-xl bg-base-200/60 p-3">
              <p class="text-xs text-base-content/50 uppercase font-semibold">Quality</p>
              <p class="mt-1 font-bold">{{ $existingReview->quality_rating ?? $existingReview->rating }}/5</p>
            </div>
            <div class="rounded-xl bg-base-200/60 p-3">
              <p class="text-xs text-base-content/50 uppercase font-semibold">Behavior</p>
              <p class="mt-1 font-bold">{{ $existingReview->behavior_rating ?? $existingReview->rating }}/5</p>
            </div>
            <div class="rounded-xl bg-base-200/60 p-3">
              <p class="text-xs text-base-content/50 uppercase font-semibold">Value</p>
              <p class="mt-1 font-bold">{{ $existingReview->value_rating ?? $existingReview->rating }}/5</p>
            </div>
          </div>
        @else
          <h2 class="text-lg font-bold text-base-content">Leave a Structured Review</h2>
          <form method="POST" action="{{ route('review.store') }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="booking_id" value="{{ $booking->id }}" />

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="label"><span class="label-text font-semibold">Punctuality</span></label>
                <select name="punctuality_rating" class="select select-bordered w-full" required>
                  @for($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}" @selected(old('punctuality_rating', 5) == $i)>{{ $i }} / 5</option>
                  @endfor
                </select>
                @error('punctuality_rating') <span class="text-error text-sm block mt-1">{{ $message }}</span> @enderror
              </div>

              <div>
                <label class="label"><span class="label-text font-semibold">Quality</span></label>
                <select name="quality_rating" class="select select-bordered w-full" required>
                  @for($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}" @selected(old('quality_rating', 5) == $i)>{{ $i }} / 5</option>
                  @endfor
                </select>
                @error('quality_rating') <span class="text-error text-sm block mt-1">{{ $message }}</span> @enderror
              </div>

              <div>
                <label class="label"><span class="label-text font-semibold">Behavior</span></label>
                <select name="behavior_rating" class="select select-bordered w-full" required>
                  @for($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}" @selected(old('behavior_rating', 5) == $i)>{{ $i }} / 5</option>
                  @endfor
                </select>
                @error('behavior_rating') <span class="text-error text-sm block mt-1">{{ $message }}</span> @enderror
              </div>

              <div>
                <label class="label"><span class="label-text font-semibold">Value</span></label>
                <select name="value_rating" class="select select-bordered w-full" required>
                  @for($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}" @selected(old('value_rating', 5) == $i)>{{ $i }} / 5</option>
                  @endfor
                </select>
                @error('value_rating') <span class="text-error text-sm block mt-1">{{ $message }}</span> @enderror
              </div>
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

    @if($isCustomer && in_array($booking->status, ['completed', 'cancelled']))
      <div class="mt-8 rounded-2xl border border-base-200 bg-base-100 p-6">
        <h2 class="text-lg font-bold text-base-content">Dispute / Complaint</h2>

        @if($myComplaint)
          <div class="mt-4 rounded-xl border border-base-200 p-4">
            <div class="flex items-center justify-between gap-3">
              <p class="font-semibold text-base-content">{{ $myComplaint->subject }}</p>
              <span class="badge badge-outline uppercase">{{ str_replace('_', ' ', $myComplaint->status) }}</span>
            </div>
            <p class="mt-2 text-base-content/70">{{ $myComplaint->details }}</p>

            @if(!empty($myComplaint->evidence_paths))
              <div class="mt-3 grid gap-2 sm:grid-cols-2">
                @foreach($myComplaint->evidence_paths as $evidence)
                  <a href="{{ asset('storage/' . ($evidence['path'] ?? '')) }}" target="_blank" class="rounded-lg border border-base-200 p-2 text-sm hover:border-primary transition-colors">
                    <p class="font-medium truncate">{{ $evidence['name'] ?? 'Evidence file' }}</p>
                    <p class="text-xs text-base-content/50">{{ $evidence['type'] ?? '' }}</p>
                  </a>
                @endforeach
              </div>
            @endif
          </div>
        @else
          <form method="POST" action="{{ route('booking.complaint', $booking) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
            @csrf
            <div>
              <label class="label"><span class="label-text font-semibold">Subject</span></label>
              <input type="text" name="subject" class="input input-bordered w-full" maxlength="160" placeholder="Brief complaint subject" value="{{ old('subject') }}" required />
              @error('subject') <span class="text-error text-sm block mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
              <label class="label"><span class="label-text font-semibold">Complaint details</span></label>
              <textarea name="details" rows="5" class="textarea textarea-bordered w-full" placeholder="Explain what went wrong, what happened, and what resolution you expect." required>{{ old('details') }}</textarea>
              @error('details') <span class="text-error text-sm block mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
              <label class="label"><span class="label-text font-semibold">Evidence files (optional)</span></label>
              <input type="file" name="evidence[]" multiple class="file-input file-input-bordered w-full" />
              <p class="mt-1 text-xs text-base-content/50">Upload up to 5 files (images, PDF, or video, max 10MB each).</p>
              @error('evidence') <span class="text-error text-sm block mt-1">{{ $message }}</span> @enderror
              @error('evidence.*') <span class="text-error text-sm block mt-1">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn btn-error btn-outline btn-sm">Submit Formal Complaint</button>
          </form>
        @endif
      </div>
    @endif

  </div>
</section>

@if($isCustomer)
  @push('scripts')
    <script>
      async function refreshTracking() {
        try {
          const response = await fetch('{{ route('booking.tracking', $booking) }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          });

          if (!response.ok) return;

          const data = await response.json();
          document.getElementById('tracking-eta-value').textContent = data.estimated_arrival_at
            ? new Date(data.estimated_arrival_at).toLocaleString()
            : 'Waiting for provider';
          document.getElementById('tracking-updated').textContent = data.tracking_updated_at
            ? `Updated ${new Date(data.tracking_updated_at).toLocaleString()}`
            : 'No tracking update yet';
          document.getElementById('tracking-location').textContent = data.provider_latitude && data.provider_longitude
            ? `${data.provider_latitude}, ${data.provider_longitude}`
            : 'Provider location not shared yet';
        } catch (error) {
          console.error(error);
        }
      }

      refreshTracking();
      setInterval(refreshTracking, 30000);
    </script>
  @endpush
@endif

@if($isProvider)
  @push('scripts')
    <script>
      function useMyLocation() {
        if (!navigator.geolocation) {
          alert('Geolocation is not supported in this browser.');
          return;
        }

        navigator.geolocation.getCurrentPosition(function(position) {
          document.querySelector('input[name="provider_latitude"]').value = position.coords.latitude.toFixed(7);
          document.querySelector('input[name="provider_longitude"]').value = position.coords.longitude.toFixed(7);
        }, function() {
          alert('Unable to read your current location.');
        });
      }
    </script>
  @endpush
@endif

@endsection

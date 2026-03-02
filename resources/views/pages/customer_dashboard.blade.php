@extends('layouts.app')

@section('content')

@php
    $currencyOptions = config('currencies.options', []);
    $currency = session('currency', config('currencies.default', 'BDT'));
    $currencyMeta = $currencyOptions[$currency] ?? ['symbol' => $currency, 'rate' => 1];
    $currencySymbol = $currencyMeta['symbol'] ?? $currency;
    $currencyRate = $currencyMeta['rate'] ?? 1;

    $nextBooking = collect($activeBookings)->sortBy('scheduled_at')->first();
@endphp


{{-- ═══════════════════ Greeting + Search ═══════════════════ --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content">Hi, {{ auth()->user()->name }}!</h2>
    <p class="mt-2 text-base text-base-content/70">Let's make life a little easier. What can we help you with today?</p>

    <div class="mt-6">
      <h3 class="text-lg font-bold text-base-content">Search for Services</h3>
      <p class="text-sm text-base-content/60">Find exactly what you need, right when you need it.</p>
      <div class="mt-3">
        <input type="text" placeholder="Search services..." class="input input-bordered w-full max-w-lg" />
      </div>
    </div>
  </div>
</section>

{{-- ═══════════════════ Dashboard Overview ═══════════════════ --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content">Dashboard Overview</h2>
    <p class="mt-2 text-base text-base-content/60">Quick insights into your HaalChaal journey.</p>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      {{-- Active Bookings --}}
      <div class="rounded-2xl bg-primary/10 p-6">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9l2 2 4-4" />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-base-content">Active Bookings</h3>
        <p class="mt-1 text-2xl font-black text-base-content">{{ $activeBookings->count() }}</p>
      </div>

      {{-- Total Spent --}}
      <div class="rounded-2xl bg-primary/10 p-6">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="1" x2="12" y2="23" /><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-base-content">Total Spent</h3>
        <p class="mt-1 text-2xl font-black text-base-content">{{ $currencySymbol }} {{ number_format($totalSpent * $currencyRate, 2) }}</p>
      </div>

      {{-- Services Used --}}
      <div class="rounded-2xl bg-primary/10 p-6">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="2" y="7" width="20" height="14" rx="2" /><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-base-content">Services Used</h3>
        <p class="mt-1 text-2xl font-black text-base-content">{{ $servicesUsed }}</p>
      </div>

      {{-- Saved Providers --}}
      <div class="rounded-2xl bg-primary/10 p-6">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-base-content">Saved Providers</h3>
        <p class="mt-1 text-2xl font-black text-base-content">{{ $savedProviders }}</p>
      </div>
    </div>
  </div>
</section>

{{-- ═══════════════════ Popular Services ═══════════════════ --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content">Popular Services</h2>
    <p class="mt-2 text-base text-base-content/60">Explore top-rated services requested by your neighbors.</p>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      @forelse($popularServices as $service)
        <div class="card bg-base-100 shadow-sm">
          @if($service->image)
            <figure class="aspect-[4/3]">
              <img src="{{ asset('images/' . $service->image) }}" alt="{{ $service->name }}" loading="lazy" class="h-full w-full object-cover" />
            </figure>
          @else
            <figure class="aspect-[4/3] bg-base-300 flex items-center justify-center">
              <svg class="h-12 w-12 text-base-content/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <rect x="2" y="7" width="20" height="14" rx="2" /><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
              </svg>
            </figure>
          @endif
          <div class="card-body p-4">
            <h3 class="text-lg font-bold text-base-content">{{ $service->name }}</h3>
            <p class="text-sm text-base-content/60">{{ Str::limit($service->description ?: 'No description.', 60) }}</p>
            <p class="text-sm font-semibold text-base-content mt-1">
              {{ $service->price ? 'Starts at ' . $currencySymbol . ' ' . number_format($service->price * $currencyRate, 2) : 'Price varies' }}
            </p>
            <div class="mt-1 flex items-center gap-1 text-sm">
              <svg class="h-4 w-4 text-warning" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <span class="text-base-content/70">{{ $service->bookings_count }} bookings</span>
            </div>
          </div>
        </div>
      @empty
        <div class="col-span-4 text-base-content/50">No popular services yet.</div>
      @endforelse
    </div>
  </div>
</section>

{{-- ═══════════════════ Active Bookings ═══════════════════ --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content">Active Bookings</h2>
    <p class="mt-2 text-base text-base-content/60">Keep track of your upcoming appointments.</p>

    <div class="mt-8 overflow-x-auto">
      <table class="table w-full">
        <thead>
          <tr class="bg-base-200">
            <th class="text-base font-bold">Service</th>
            <th class="text-base font-bold">Scheduled Time</th>
            <th class="text-base font-bold">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($activeBookings as $booking)
            <tr class="hover">
              <td class="font-medium">{{ optional($booking->service)->name ?: 'Service' }}</td>
              <td>{{ $booking->scheduled_at ? $booking->scheduled_at->format('D, g:i A') : 'Not scheduled' }}</td>
              <td>
                @php
                  $statusClass = match(strtolower($booking->status)) {
                    'confirmed' => 'badge-primary',
                    'pending'   => 'badge-outline',
                    'active'    => 'badge-info',
                    default     => 'badge-ghost',
                  };
                @endphp
                <span class="badge {{ $statusClass }} uppercase text-xs font-semibold">{{ $booking->status }}</span>
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-base-content/50">No active bookings.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-4">
      <a href="{{ route('customer.history') }}" class="btn btn-outline btn-primary btn-sm">View All Bookings</a>
    </div>
  </div>
</section>

{{-- ═══════════════════ Recent Reviews ═══════════════════ --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content">Recent Reviews</h2>
    <p class="mt-2 text-base text-base-content/60">See what others are saying about our top providers.</p>

    <div class="mt-8 space-y-6">
      @forelse($reviews as $review)
        <div class="border-l-4 border-primary pl-6">
          <p class="text-base text-base-content/80 italic">"{{ $review->comment ?: 'No comment provided.' }}"</p>
          <div class="mt-2 flex items-center gap-2 text-sm text-base-content/60">
            <svg class="h-4 w-4 text-warning" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <span class="font-semibold">{{ $review->rating }}.0</span>
            <span>- {{ optional($review->taker)->name ?: 'Customer' }}</span>
          </div>
        </div>
      @empty
        <p class="text-base-content/50">No reviews yet.</p>
      @endforelse
    </div>

    <div class="mt-6">
      <a href="#" class="btn btn-outline btn-primary btn-sm">Write a Review</a>
    </div>
  </div>
</section>

{{-- ═══════════════════ Support & Recommendations ═══════════════════ --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <span class="badge badge-ghost text-xs font-semibold uppercase">Get Help</span>
    <h2 class="mt-2 text-3xl font-bold text-base-content">Support &amp; Recommendations</h2>
    <p class="mt-2 text-base text-base-content/60">Need a hand? We're here for you.</p>

    <div class="mt-8 grid items-start gap-10 lg:grid-cols-2">
      {{-- Left: Support --}}
      <div class="overflow-hidden rounded-2xl bg-base-100 shadow-xl">
        <img src="{{ asset('images/support.png') }}" alt="Support" loading="lazy" class="w-full" />
      </div>

      {{-- Right: Sidebar cards --}}
      <div class="space-y-6">
        {{-- Upcoming Booking --}}
        <div class="rounded-2xl bg-warning/10 p-6">
          <h3 class="text-xl font-bold text-base-content">Upcoming Booking</h3>
          @if($nextBooking)
            <p class="mt-2 text-base text-base-content/70">
              {{ optional($nextBooking->service)->name ?: 'Service' }}
              with {{ optional($nextBooking->provider)->name ?: 'Provider' }}
            </p>
            <p class="text-sm text-base-content/60">
              {{ $nextBooking->scheduled_at ? $nextBooking->scheduled_at->format('l, g:i A') : 'Not scheduled' }}
            </p>
          @else
            <p class="mt-2 text-base text-base-content/50">No upcoming bookings.</p>
          @endif
          <a href="{{ route('customer.history') }}" class="btn btn-primary btn-sm mt-4">Manage Booking</a>
        </div>

        {{-- Recommended Providers --}}
        <div class="rounded-2xl border border-base-300 bg-base-100 p-6">
          <h3 class="text-xl font-bold text-base-content">Recommended Providers</h3>
          <p class="mt-1 text-sm text-base-content/60">Based on your preferences and past bookings.</p>
          <ul class="mt-3 list-disc list-inside space-y-1 text-base text-base-content/70">
            <li>ProFix Handyman</li>
            <li>Green Thumb Landscaping</li>
          </ul>
          <a href="{{ route('customer.browse') }}" class="btn btn-outline btn-sm mt-4">Explore More</a>
        </div>

        {{-- Support & Help --}}
        <div class="rounded-2xl border border-base-300 bg-base-100 p-6">
          <h3 class="text-xl font-bold text-base-content">Support &amp; Help</h3>
          <p class="mt-1 text-sm text-base-content/60">Find answers or contact us directly.</p>
          <a href="{{ route('home') }}" class="btn btn-primary btn-sm mt-4">Get Help</a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ═══════════════════ CTA Footer ═══════════════════ --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-black text-base-content ">Always Here For You</h2>
    <p class="mt-2 text-base text-base-content/70">Seamless service, every time. Experience the HaalChaal difference.</p>
    <a href="{{ route('customer.browse') }}" class="btn btn-primary btn-lg mt-6">Book Your Next Service</a>
  </div>
</section>

@endsection
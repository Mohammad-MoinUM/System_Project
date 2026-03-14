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
    <h2 class="text-3xl font-bold text-base-content scroll-fade-up">Hi, {{ auth()->user()->name }}!</h2>
    <p class="mt-2 text-base text-base-content/70 scroll-fade-up" style="transition-delay:.05s">Let's make life a little easier. What can we help you with today?</p>

    <div class="mt-6">
      <h3 class="text-lg font-bold text-base-content">Search for Services</h3>
      <p class="text-sm text-base-content/60">Find exactly what you need, right when you need it.</p>
      <div class="mt-3 relative w-full max-w-lg" id="search-wrapper">
        <input
          type="text"
          id="service-search"
          placeholder="Search services... (e.g. Home, Cleaning, Plumbing)"
          autocomplete="off"
          class="input input-bordered w-full"
        />
        <div id="search-suggestions" class="absolute z-50 top-full left-0 right-0 mt-1 bg-base-100 rounded-xl shadow-xl border border-base-300 hidden max-h-80 overflow-y-auto"></div>
      </div>
    </div>
  </div>
</section>

{{-- ═══════════════════ Dashboard Overview ═══════════════════ --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-bold text-base-content">Dashboard Overview</h2>
        <p class="mt-2 text-base text-base-content/60">Quick insights into your HaalChaal journey.</p>
      </div>
      <form method="POST" action="{{ route('currency.set') }}">
        @csrf
        <select name="currency" onchange="this.form.submit()"
                class="select select-bordered select-sm">
          @foreach ($currencyOptions as $code => $meta)
            <option value="{{ $code }}" {{ $currency === $code ? 'selected' : '' }}>
              {{ $meta['symbol'] }} {{ $code }}
            </option>
          @endforeach
        </select>
      </form>
    </div>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      {{-- Active Bookings --}}
      <div class="rounded-2xl bg-primary/10 p-6 scroll-fade-up" style="transition-delay:.05s">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
          <x-heroicon-o-clipboard-document-check class="h-6 w-6" />
        </div>
        <h3 class="text-lg font-bold text-base-content">Active Bookings</h3>
        <p class="mt-1 text-2xl font-black text-base-content" data-count-to="{{ $activeBookings->count() }}">0</p>
      </div>

      {{-- Total Spent --}}
      <div class="rounded-2xl bg-primary/10 p-6 scroll-fade-up" style="transition-delay:.1s">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
          <x-heroicon-o-currency-dollar class="h-6 w-6" />
        </div>
        <h3 class="text-lg font-bold text-base-content">Total Spent</h3>
        <p class="mt-1 text-2xl font-black text-base-content" data-count-to="{{ $totalSpent * $currencyRate }}" data-count-prefix="{{ $currencySymbol }} " data-count-decimals="2">{{ $currencySymbol }} 0.00</p>
      </div>

      {{-- Services Used --}}
      <div class="rounded-2xl bg-primary/10 p-6 scroll-fade-up" style="transition-delay:.15s">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
          <x-heroicon-o-briefcase class="h-6 w-6" />
        </div>
        <h3 class="text-lg font-bold text-base-content">Services Used</h3>
        <p class="mt-1 text-2xl font-black text-base-content" data-count-to="{{ $servicesUsed }}">0</p>
      </div>

      {{-- Saved Providers --}}
      <div class="rounded-2xl bg-primary/10 p-6 scroll-fade-up" style="transition-delay:.2s">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
          <x-heroicon-o-heart class="h-6 w-6" />
        </div>
        <h3 class="text-lg font-bold text-base-content">Saved Providers</h3>
        <p class="mt-1 text-2xl font-black text-base-content" data-count-to="{{ $savedProviders }}">0</p>
      </div>
    </div>
  </div>
</section>

{{-- ═══════════════════ Popular Services ═══════════════════ --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content scroll-fade-up">Popular Services</h2>
    <p class="mt-2 text-base text-base-content/60 scroll-fade-up" style="transition-delay:.05s">Explore top-rated services requested by your neighbors.</p>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      @forelse($popularServices as $service)
        <a href="{{ route('customer.browse.category', ['category' => $service->category]) }}" class="card bg-base-100 shadow-sm hover:shadow-lg transition-shadow scroll-zoom-in" style="transition-delay:{{ $loop->index * 0.08 }}s">
          @php $unsplashImage = $serviceImages[$service->name] ?? null; @endphp
          @if($unsplashImage)
            <figure class="aspect-[4/3] overflow-hidden">
              <img src="{{ $unsplashImage }}" alt="{{ $service->name }}" loading="lazy" class="h-full w-full object-cover" />
            </figure>
          @else
            <figure class="aspect-[4/3] bg-base-300 flex items-center justify-center">
              <x-heroicon-o-briefcase class="h-12 w-12 text-base-content/30" />
            </figure>
          @endif
          <div class="card-body p-4">
            <h3 class="text-lg font-bold text-base-content">{{ $service->name }}</h3>
            <p class="text-sm text-base-content/60">{{ Str::limit($service->description ?: 'No description.', 60) }}</p>
            <p class="text-sm font-semibold text-base-content mt-1">
              {{ $service->price ? 'Starts at ' . $currencySymbol . ' ' . number_format($service->price * $currencyRate, 2) : 'Price varies' }}
            </p>
            <div class="mt-1 flex items-center gap-2 text-sm">
              <div class="flex items-center gap-1">
                <x-heroicon-s-star class="h-4 w-4 text-warning" />
                <span class="text-base-content/70">{{ number_format($service->avg_rating, 1) }}</span>
              </div>
              <span class="text-base-content/30">·</span>
              <span class="text-base-content/70">{{ $service->reviews_count }} {{ Str::plural('review', $service->reviews_count) }}</span>
              <span class="text-base-content/30">·</span>
              <span class="text-base-content/70">{{ $service->bookings_count }} {{ Str::plural('booking', $service->bookings_count) }}</span>
            </div>
          </div>
        </a>
      @empty
        <div class="col-span-4 text-base-content/50">No popular services yet.</div>
      @endforelse
    </div>
  </div>
</section>

{{-- ═══════════════════ Active Bookings ═══════════════════ --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content scroll-fade-up">Active Bookings</h2>
    <p class="mt-2 text-base text-base-content/60 scroll-fade-up" style="transition-delay:.05s">Keep track of your upcoming appointments.</p>

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
    <h2 class="text-3xl font-bold text-base-content scroll-fade-up">Recent Reviews</h2>
    <p class="mt-2 text-base text-base-content/60 scroll-fade-up" style="transition-delay:.05s">See what others are saying about our top providers.</p>

    <div class="mt-8 space-y-6">
      @forelse($reviews as $review)
        <div class="border-l-4 border-primary pl-6 scroll-fade-left" style="transition-delay:{{ $loop->index * 0.1 }}s">
          <p class="text-base text-base-content/80 italic">"{{ $review->comment ?: 'No comment provided.' }}"</p>
          <div class="mt-2 flex items-center gap-2 text-sm text-base-content/60">
            <x-heroicon-s-star class="h-4 w-4 text-warning" />
            <span class="font-semibold">{{ $review->rating }}.0</span>
            <span>- {{ optional($review->taker)->name ?: 'Customer' }}</span>
          </div>
        </div>
      @empty
        <p class="text-base-content/50">No reviews yet.</p>
      @endforelse
    </div>

    <div class="mt-6">
      <a href="{{ route('customer.history') }}" class="btn btn-outline btn-primary btn-sm">View All Bookings</a>
    </div>
  </div>
</section>

{{-- ═══════════════════ Support & Recommendations ═══════════════════ --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <span class="badge badge-ghost text-xs font-semibold uppercase">Get Help</span>
    <h2 class="mt-2 text-3xl font-bold text-base-content scroll-fade-up">Support &amp; Recommendations</h2>
    <p class="mt-2 text-base text-base-content/60 scroll-fade-up" style="transition-delay:.05s">Need a hand? We're here for you.</p>

    <div class="mt-8 grid items-start gap-10 lg:grid-cols-2">
      {{-- Left: Support --}}
      <div class="overflow-hidden rounded-2xl bg-base-100 shadow-xl scroll-fade-left">
        <img src="{{ asset('images/support.png') }}" alt="Support" loading="lazy" class="w-full" />
      </div>

      {{-- Right: Sidebar cards --}}
      <div class="space-y-6 scroll-fade-right" style="transition-delay:.15s">
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
    <h2 class="text-3xl font-black text-base-content  scroll-fade-up">Always Here For You</h2>
    <p class="mt-2 text-base text-base-content/70 scroll-fade-up" style="transition-delay:.05s">Seamless service, every time. Experience the HaalChaal difference.</p>
    <a href="{{ route('customer.browse') }}" class="btn btn-primary btn-lg mt-6 scroll-fade-up" style="transition-delay:.1s">Book Your Next Service</a>
  </div>
</section>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('service-search');
    const box = document.getElementById('search-suggestions');
    let timer = null;

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { box.classList.add('hidden'); box.innerHTML = ''; return; }
        timer = setTimeout(() => fetchSuggestions(q), 300);
    });

    async function fetchSuggestions(q) {
        try {
            const res = await fetch(`{{ route('customer.browse.suggest') }}?q=${encodeURIComponent(q)}`);
            const data = await res.json();
            if (!data.length) {
                box.innerHTML = '<div class="p-4 text-sm text-base-content/50">No services found.</div>';
                box.classList.remove('hidden');
                return;
            }
            box.innerHTML = data.map(item => `
                <a href="${item.url}" class="flex items-center gap-3 px-4 py-3 hover:bg-base-200 transition-colors first:rounded-t-xl last:rounded-b-xl">
                    ${item.image
                        ? `<img src="${item.image}" alt="${item.name}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0" />`
                        : `<div class="w-12 h-12 rounded-lg bg-base-300 flex items-center justify-center flex-shrink-0"><svg class="w-6 h-6 text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0" /></svg></div>`}
                    <div class="min-w-0">
                        <p class="font-semibold text-base-content truncate">${item.name}</p>
                        <p class="text-xs text-base-content/50">${item.count} service${item.count !== 1 ? 's' : ''} available</p>
                    </div>
                </a>
            `).join('');
            box.classList.remove('hidden');
        } catch (e) {
            box.classList.add('hidden');
        }
    }

    document.addEventListener('click', function (e) {
        if (!document.getElementById('search-wrapper').contains(e.target)) {
            box.classList.add('hidden');
        }
    });
});
</script>
@endpush
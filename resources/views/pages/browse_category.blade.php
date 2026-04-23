@extends('layouts.app')

@section('title', $category . ' — Browse Providers')
@section('content')

@php
    $currencyOptions = config('currencies.options', []);
    $currency = session('currency', config('currencies.default', 'BDT'));
    $currencyMeta = $currencyOptions[$currency] ?? ['symbol' => $currency, 'rate' => 1];
    $currencySymbol = $currencyMeta['symbol'] ?? $currency;
    $currencyRate = $currencyMeta['rate'] ?? 1;
@endphp

{{-- ═══════════════════ Category Hero ═══════════════════ --}}
<section class="relative overflow-hidden">
  <div class="absolute inset-0">
    @if($categoryImage)
      <img src="{{ $categoryImage }}" alt="{{ $category }}" class="h-full w-full object-cover" />
    @else
      <div class="h-full w-full bg-gradient-to-br from-primary/30 to-secondary/30"></div>
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-black/30"></div>
  </div>

  <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <a href="{{ route('customer.browse') }}" class="inline-flex items-center gap-1.5 text-sm text-white/70 hover:text-white mb-4 transition-colors">
      <x-heroicon-o-arrow-left class="w-4 h-4" />
      Back to categories
    </a>
    <h1 class="text-4xl font-bold text-white">{{ $category }}</h1>
    <p class="mt-2 text-lg text-white/70">
      {{ $providers->count() }} {{ Str::plural('provider', $providers->count()) }} available
    </p>
  </div>
</section>

{{-- ═══════════════════ Filters ═══════════════════ --}}
<section class="bg-base-200 border-b border-base-300">
  <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
    <form method="GET" action="{{ route('customer.browse.category', $category) }}" class="flex flex-wrap items-end gap-4">

      {{-- Sort --}}
      <div>
        <label class="text-xs font-semibold text-base-content/50 mb-1 block">Sort By</label>
        <select name="sort" class="select select-bordered select-sm">
          <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Most Popular</option>
          <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>Highest Rated</option>
          <option value="price_low" {{ $sort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
          <option value="price_high" {{ $sort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
          <option value="experience" {{ $sort === 'experience' ? 'selected' : '' }}>Most Experienced</option>
        </select>
      </div>

      {{-- Price Range --}}
      <div>
        <label class="text-xs font-semibold text-base-content/50 mb-1 block">Min Price</label>
        <input type="number" name="min_price" value="{{ $minPrice }}" placeholder="0" min="0"
               class="input input-bordered input-sm w-28" />
      </div>
      <div>
        <label class="text-xs font-semibold text-base-content/50 mb-1 block">Max Price</label>
        <input type="number" name="max_price" value="{{ $maxPrice }}" placeholder="Any" min="0"
               class="input input-bordered input-sm w-28" />
      </div>

      {{-- City filter --}}
      @if($availableCities->count() > 1)
        <div>
          <label class="text-xs font-semibold text-base-content/50 mb-1 block">City</label>
          <select name="city" class="select select-bordered select-sm">
            <option value="">All Cities</option>
            @foreach($availableCities as $c)
              <option value="{{ $c }}" {{ $city === $c ? 'selected' : '' }}>{{ $c }}</option>
            @endforeach
          </select>
        </div>
      @endif

      <div>
        <label class="text-xs font-semibold text-base-content/50 mb-1 block">Availability</label>
        <select name="availability" class="select select-bordered select-sm">
          <option value="any" {{ $availability === 'any' ? 'selected' : '' }}>Any Time</option>
          <option value="available_today" {{ $availability === 'available_today' ? 'selected' : '' }}>Available Today</option>
          <option value="available_week" {{ $availability === 'available_week' ? 'selected' : '' }}>Available This Week</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-sm">Apply</button>
      <a href="{{ route('customer.browse.category', $category) }}" class="btn btn-ghost btn-sm">Reset</a>
    </form>
  </div>
</section>

{{-- ═══════════════════ Provider Cards ═══════════════════ --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="space-y-6">
      @forelse($providers as $provider)
        <div class="card card-side bg-base-100 border border-base-200 shadow-sm hover:shadow-md transition-shadow">
          {{-- Provider Photo --}}
          <figure class="w-32 sm:w-48 flex-shrink-0 bg-base-300">
            @if($provider->user->photo)
              <img src="{{ asset('storage/' . $provider->user->photo) }}"
                   alt="{{ $provider->user->first_name }}"
                   class="h-full w-full object-cover" />
            @else
              <div class="h-full w-full flex items-center justify-center text-base-content/20">
                <x-heroicon-o-user class="w-16 h-16" />
              </div>
            @endif
          </figure>

          <div class="card-body p-5">
            {{-- Header --}}
            <div class="flex flex-wrap items-start justify-between gap-2">
              <div>
                <h3 class="text-lg font-bold text-base-content">
                  {{ $provider->user->first_name }} {{ $provider->user->last_name }}
                </h3>
                <div class="flex items-center gap-3 mt-1">
                    @if(($provider->user->verification_status ?? null) === 'approved')
                      <span class="badge badge-success badge-sm">Verified</span>
                    @endif
                  {{-- Rating --}}
                  <div class="flex items-center gap-1">
                    @if($provider->avg_rating)
                      <x-heroicon-s-star class="w-4 h-4 text-warning" />
                      <span class="text-sm font-semibold">{{ number_format($provider->avg_rating, 1) }}</span>
                      <span class="text-xs text-base-content/50">({{ $provider->review_count }})</span>
                    @else
                      <span class="text-xs text-base-content/40">No reviews yet</span>
                    @endif
                  </div>
                  {{-- Location --}}
                  @if($provider->user->city || $provider->user->area)
                    <span class="text-xs text-base-content/50 flex items-center gap-1">
                      <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                      {{ collect([$provider->user->area, $provider->user->city])->filter()->implode(', ') }}
                    </span>
                  @endif
                </div>
              </div>

              {{-- Stats badges --}}
              <div class="flex gap-2">
                @if($provider->total_bookings > 0)
                  <span class="badge badge-ghost badge-sm">{{ $provider->total_bookings }} jobs done</span>
                @endif
                @if($provider->user->experience_years)
                  <span class="badge badge-ghost badge-sm">{{ $provider->user->experience_years }}+ yrs</span>
                @endif
              </div>
            </div>

            {{-- Bio --}}
            @if($provider->user->bio)
              <p class="text-sm text-base-content/60 mt-2">{{ Str::limit($provider->user->bio, 120) }}</p>
            @endif

            {{-- Services in this category --}}
            <div class="mt-3">
              <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wider">Services in {{ $category }}</span>
              <div class="mt-2 flex flex-wrap gap-2">
                @foreach($provider->services as $service)
                  <div class="rounded-lg bg-base-200 px-3 py-1.5 text-sm">
                    <span class="font-medium text-base-content">{{ $service->name }}</span>
                    @if(!empty($service->flash_deal_price) && $service->flash_deal_ends_at && now()->lt($service->flash_deal_ends_at))
                      <span class="text-primary font-semibold ml-1">
                        {{ $currencySymbol }} {{ number_format($service->flash_deal_price * $currencyRate, 0) }}
                      </span>
                      <span class="badge badge-warning badge-xs ml-1">Flash</span>
                    @elseif($service->price)
                      <span class="text-primary font-semibold ml-1">
                        {{ $currencySymbol }} {{ number_format($service->price * $currencyRate, 0) }}
                      </span>
                    @else
                      <span class="text-base-content/40 ml-1">Price varies</span>
                    @endif
                    @if($service->is_insured)
                      <span class="badge badge-info badge-xs ml-1">Insured</span>
                    @endif
                    @if($service->guarantee_enabled)
                      <span class="badge badge-success badge-xs ml-1">Guaranteed</span>
                    @endif
                  </div>
                @endforeach
              </div>
            </div>

            {{-- Starting price --}}
            @if($provider->min_price)
              <div class="mt-3 text-sm">
                <span class="text-base-content/50">Starting from</span>
                <span class="text-lg font-bold text-primary ml-1">
                  {{ $currencySymbol }} {{ number_format($provider->min_price * $currencyRate, 0) }}
                </span>
              </div>
            @endif

            {{-- Action Buttons --}}
            <div class="mt-4 flex flex-wrap items-center gap-2">
              @foreach($provider->services->take(1) as $service)
                <a href="{{ route('booking.create', $service) }}" class="btn btn-primary btn-sm">
                  <x-heroicon-o-calendar class="w-4 h-4" /> Book Now
                </a>
              @endforeach

              @auth
                @if(Auth::user()->role === 'customer')
                  <form method="POST" action="{{ route('customer.saved.toggle', $provider->user) }}">
                    @csrf
                    @php
                      $isSaved = Auth::user()->savedProviders->contains('provider_id', $provider->user->id);
                    @endphp
                    <button type="submit" class="btn btn-outline btn-sm {{ $isSaved ? 'btn-secondary' : '' }}">
                      @if($isSaved)
                        <x-heroicon-s-heart class="w-4 h-4" /> Saved
                      @else
                        <x-heroicon-o-heart class="w-4 h-4" /> Save
                      @endif
                    </button>
                  </form>
                @endif
              @endauth
            </div>
          </div>
        </div>
      @empty
        <div class="text-center py-16">
          <x-heroicon-o-user-group class="w-12 h-12 text-base-content/20 mx-auto" />
          <p class="mt-4 text-lg text-base-content/50">No providers found</p>
          <p class="text-sm text-base-content/40 mt-1">
            @if($minPrice || $maxPrice || $city)
              Try adjusting your filters or
              <a href="{{ route('customer.browse.category', $category) }}" class="text-primary hover:underline">reset all filters</a>.
            @else
              No providers have registered services in this category yet.
            @endif
          </p>
          <a href="{{ route('customer.browse') }}" class="btn btn-primary btn-sm mt-6">Browse Other Categories</a>
        </div>
      @endforelse
    </div>
  </div>
</section>

@endsection

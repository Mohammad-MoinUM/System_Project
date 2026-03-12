@extends('layouts.app')

@section('title', 'Browse Services')
@section('content')

@php
    $currencyOptions = config('currencies.options', []);
    $currency = session('currency', config('currencies.default', 'BDT'));
    $currencyMeta = $currencyOptions[$currency] ?? ['symbol' => $currency, 'rate' => 1];
    $currencySymbol = $currencyMeta['symbol'] ?? $currency;
@endphp

{{-- ═══════════════════ Hero / Search ═══════════════════ --}}
<section class="bg-gradient-to-br from-primary/10 to-secondary/10">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 text-center">
    <h1 class="text-4xl font-bold text-base-content">Find the Right Service</h1>
    <p class="mt-3 text-lg text-base-content/60 max-w-xl mx-auto">Browse service categories and connect with trusted providers in your area.</p>

    <form method="GET" action="{{ route('customer.browse') }}" class="mt-8 flex items-center gap-3 max-w-lg mx-auto">
      <div class="relative flex-1">
        <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-base-content/30" />
        <input type="text" name="q" value="{{ $search }}"
               placeholder="Search categories... (e.g. Cleaning, Plumbing)"
               class="input input-bordered w-full pl-10" />
      </div>
      <button type="submit" class="btn btn-primary">Search</button>
    </form>

    @if($search)
      <div class="mt-4">
        <a href="{{ route('customer.browse') }}" class="text-sm text-primary hover:underline">Clear search</a>
      </div>
    @endif
  </div>
</section>

{{-- ═══════════════════ Category Grid ═══════════════════ --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h2 class="text-2xl font-bold text-base-content">Service Categories</h2>
        <p class="text-sm text-base-content/60 mt-1">
          {{ $categories->count() }} {{ Str::plural('category', $categories->count()) }} available
        </p>
      </div>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      @forelse($categories as $cat)
        @php
          $catName = $cat->category ?: 'Other';
          $image = $categoryImages[$catName] ?? null;
        @endphp

        <a href="{{ route('customer.browse.category', ['category' => $catName]) }}"
           class="group relative overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 bg-base-200">
          {{-- Image --}}
          <div class="aspect-[4/3] overflow-hidden">
            @if($image)
              <img src="{{ $image }}"
                   alt="{{ $catName }}"
                   loading="lazy"
                   class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
            @else
              <div class="h-full w-full bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center">
                <x-heroicon-o-briefcase class="w-16 h-16 text-base-content/20" />
              </div>
            @endif
          </div>

          {{-- Overlay --}}
          <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

          {{-- Content --}}
          <div class="absolute bottom-0 left-0 right-0 p-5">
            <h3 class="text-lg font-bold text-white">{{ $catName }}</h3>
            <div class="flex items-center gap-3 mt-1.5">
              <span class="text-xs text-white/80 flex items-center gap-1">
                <x-heroicon-o-briefcase class="w-3.5 h-3.5" />
                {{ $cat->services_count }} {{ Str::plural('service', $cat->services_count) }}
              </span>
              <span class="text-xs text-white/80 flex items-center gap-1">
                <x-heroicon-o-user-group class="w-3.5 h-3.5" />
                {{ $cat->providers_count }} {{ Str::plural('provider', $cat->providers_count) }}
              </span>
            </div>
          </div>

          {{-- Hover arrow --}}
          <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
            <div class="rounded-full bg-white/20 backdrop-blur p-2">
              <x-heroicon-o-arrow-right class="w-4 h-4 text-white" />
            </div>
          </div>
        </a>
      @empty
        <div class="col-span-full text-center py-16">
          <x-heroicon-o-magnifying-glass class="w-12 h-12 text-base-content/20 mx-auto" />
          <p class="mt-4 text-lg text-base-content/50">
            @if($search)
              No categories match "{{ $search }}"
            @else
              No service categories available yet
            @endif
          </p>
          <p class="text-sm text-base-content/40 mt-1">Check back later as providers register their services.</p>
        </div>
      @endforelse
    </div>
  </div>
</section>

@endsection

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
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-base-content">My Services</h1>
        <p class="mt-2 text-base-content/60">Manage the services you offer to customers.</p>
      </div>
      <a href="{{ route('provider.services.create') }}" class="btn btn-primary btn-sm">
        <x-heroicon-o-plus class="w-4 h-4" />
        Add Service
      </a>
    </div>
  </div>
</section>

<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    @if(session('success'))
      <div class="alert alert-success mb-6">{{ session('success') }}</div>
    @endif

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      @forelse($services as $service)
        <div class="card bg-base-100 border border-base-200 shadow-sm {{ !$service->is_active ? 'opacity-60' : '' }}">
          <div class="card-body">
            <div class="flex items-start justify-between">
              <div>
                <h3 class="text-lg font-bold text-base-content">{{ $service->name }}</h3>
                <span class="badge badge-ghost badge-sm">{{ $service->category }}</span>
              </div>
              <div class="flex items-center gap-1">
                @if($service->is_active)
                  <span class="badge badge-success badge-xs">Active</span>
                @else
                  <span class="badge badge-ghost badge-xs">Inactive</span>
                @endif
              </div>
            </div>

            @if($service->description)
              <p class="text-sm text-base-content/60 mt-2">{{ Str::limit($service->description, 100) }}</p>
            @endif

            <div class="mt-3 flex items-center justify-between">
              <span class="text-xl font-bold text-primary">
                {{ $currencySymbol }} {{ number_format(($service->price ?? 0) * $currencyRate, 2) }}
              </span>
              <span class="text-xs text-base-content/40">{{ $service->bookings_count }} {{ Str::plural('booking', $service->bookings_count) }}</span>
            </div>

            <div class="card-actions justify-between items-center mt-4 pt-3 border-t border-base-200">
              <div class="flex gap-2">
                <a href="{{ route('provider.services.edit', $service) }}" class="btn btn-ghost btn-xs">Edit</a>
                <form method="POST" action="{{ route('provider.services.toggle', $service) }}">
                  @csrf
                  <button type="submit" class="btn btn-ghost btn-xs">
                    {{ $service->is_active ? 'Disable' : 'Enable' }}
                  </button>
                </form>
              </div>
              <form method="POST" action="{{ route('provider.services.destroy', $service) }}" onsubmit="return confirm('Delete this service?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-ghost btn-xs text-error">Delete</button>
              </form>
            </div>
          </div>
        </div>
      @empty
        <div class="col-span-full text-center py-16">
          <x-heroicon-o-wrench-screwdriver class="w-12 h-12 text-base-content/20 mx-auto" />
          <p class="mt-4 text-lg text-base-content/50">You haven't added any services yet.</p>
          <a href="{{ route('provider.services.create') }}" class="btn btn-primary btn-sm mt-4">Add Your First Service</a>
        </div>
      @endforelse
    </div>
  </div>
</section>

@endsection

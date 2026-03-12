@extends('layouts.app')

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-primary/10 to-secondary/10">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 text-center">
    <h1 class="text-4xl font-bold text-base-content scroll-fade-up">Our Services</h1>
    <p class="mt-3 text-lg text-base-content/60 max-w-2xl mx-auto scroll-fade-up" style="transition-delay:.1s">Browse through our wide range of professional services available in your area.</p>
  </div>
</section>

{{-- Categories --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      @forelse($categories as $cat)
        <div class="card bg-base-100 border border-base-200 shadow-sm hover:shadow-lg transition-shadow scroll-zoom-in" style="transition-delay:{{ $loop->index * 0.08 }}s">
          <div class="card-body">
            <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
              <x-heroicon-o-briefcase class="h-6 w-6" />
            </div>
            <h3 class="text-xl font-bold text-base-content">{{ $cat->category }}</h3>
            <p class="text-sm text-base-content/60">{{ $cat->services_count }} {{ Str::plural('service', $cat->services_count) }} available</p>
            @auth
              <div class="card-actions justify-end mt-4">
                <a href="{{ route('customer.browse.category', $cat->category) }}" class="btn btn-primary btn-sm">Browse Providers</a>
              </div>
            @else
              <div class="card-actions justify-end mt-4">
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Get Started</a>
              </div>
            @endauth
          </div>
        </div>
      @empty
        <div class="col-span-full text-center py-16">
          <x-heroicon-o-briefcase class="w-12 h-12 text-base-content/20 mx-auto" />
          <p class="mt-4 text-lg text-base-content/50">No services available yet.</p>
        </div>
      @endforelse
    </div>
  </div>
</section>

@endsection

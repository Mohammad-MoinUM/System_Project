@extends('layouts.app')

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-primary/10 to-secondary/10">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 text-center">
    <h1 class="text-4xl font-bold text-base-content scroll-fade-up">About HaalChaal</h1>
    <p class="mt-3 text-lg text-base-content/60 max-w-2xl mx-auto scroll-fade-up" style="transition-delay:.1s">Your trusted local service marketplace connecting customers with skilled professionals.</p>
  </div>
</section>

{{-- Mission --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid gap-12 lg:grid-cols-2 items-center">
      <div class="scroll-fade-left">
        <span class="badge badge-primary badge-outline text-xs font-semibold uppercase">Our Mission</span>
        <h2 class="mt-4 text-3xl font-bold text-base-content">Making Service Access Effortless</h2>
        <p class="mt-4 text-base-content/60">HaalChaal was built with a simple goal: to make it easy for people to find reliable, affordable service providers in their local area. Whether you need home cleaning, plumbing, tutoring, or any other service — we've got you covered.</p>
        <p class="mt-4 text-base-content/60">We believe everyone deserves access to quality services, and every skilled professional deserves a platform to showcase their expertise and grow their business.</p>
      </div>
      <div class="grid grid-cols-2 gap-6">
        <div class="rounded-2xl bg-primary/10 p-6 text-center scroll-zoom-in" style="transition-delay:.1s">
          <p class="text-3xl font-black text-primary" data-count-to="100" data-count-suffix="+">0</p>
          <p class="mt-1 text-sm text-base-content/60">Service Categories</p>
        </div>
        <div class="rounded-2xl bg-primary/10 p-6 text-center scroll-zoom-in" style="transition-delay:.2s">
          <p class="text-3xl font-black text-primary" data-count-to="500" data-count-suffix="+">0</p>
          <p class="mt-1 text-sm text-base-content/60">Verified Providers</p>
        </div>
        <div class="rounded-2xl bg-primary/10 p-6 text-center scroll-zoom-in" style="transition-delay:.3s">
          <p class="text-3xl font-black text-primary" data-count-to="10" data-count-suffix="K+">0</p>
          <p class="mt-1 text-sm text-base-content/60">Bookings Completed</p>
        </div>
        <div class="rounded-2xl bg-primary/10 p-6 text-center scroll-zoom-in" style="transition-delay:.4s">
          <p class="text-3xl font-black text-primary" data-count-to="4.8" data-count-decimals="1">0</p>
          <p class="mt-1 text-sm text-base-content/60">Avg. Rating</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Values --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content text-center scroll-fade-up">What We Stand For</h2>
    <div class="mt-12 grid gap-8 sm:grid-cols-3">
      <div class="rounded-2xl bg-base-100 p-6 shadow-sm text-center scroll-fade-up" style="transition-delay:.1s">
        <div class="mx-auto mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
          <x-heroicon-o-shield-check class="h-6 w-6" />
        </div>
        <h3 class="text-lg font-bold text-base-content">Trust & Safety</h3>
        <p class="mt-2 text-sm text-base-content/60">All providers are verified. Transparent reviews and ratings help you make informed decisions.</p>
      </div>
      <div class="rounded-2xl bg-base-100 p-6 shadow-sm text-center scroll-fade-up" style="transition-delay:.2s">
        <div class="mx-auto mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
          <x-heroicon-o-hand-thumb-up class="h-6 w-6" />
        </div>
        <h3 class="text-lg font-bold text-base-content">Quality First</h3>
        <p class="mt-2 text-sm text-base-content/60">We maintain high standards for service quality through our review system and performance tracking.</p>
      </div>
      <div class="rounded-2xl bg-base-100 p-6 shadow-sm text-center scroll-fade-up" style="transition-delay:.3s">
        <div class="mx-auto mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
          <x-heroicon-o-users class="h-6 w-6" />
        </div>
        <h3 class="text-lg font-bold text-base-content">Community</h3>
        <p class="mt-2 text-sm text-base-content/60">We strengthen local communities by connecting neighbors with skilled local professionals.</p>
      </div>
    </div>
  </div>
</section>

@endsection

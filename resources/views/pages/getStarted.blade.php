@extends('layouts.app')

@section('hideNavbar', true)

@section('content')

<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid items-center gap-10 lg:grid-cols-2">
      <div class="space-y-6 scroll-fade-left">
        <h1 class="text-4xl font-black leading-tight text-base-content sm:text-5xl lg:text-6xl">
          HaalChaal: Your Local
          <span class="block">Service Marketplace</span>
        </h1>
        <p class="text-base text-base-content/70 sm:text-lg">
          Connecting you with trusted local service providers for every need,
          effortlessly.
        </p>
        <div class="flex flex-col gap-4 sm:flex-row">
          <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Get Started</a>
          <a href="{{ route('login') }}" class="btn btn-outline btn-lg">Learn More</a>
        </div>
      </div>
      <div class="overflow-hidden rounded-3xl bg-base-100 shadow-xl scroll-fade-right" style="transition-delay:.15s">
        <img src="{{ asset('images/getstarted.jpg') }}" alt="Local services" class="h-full w-full object-cover" />
      </div>
    </div>
  </div>
</section>

<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid items-center gap-10 lg:grid-cols-2">
      <div class="overflow-hidden rounded-3xl bg-base-200 shadow-xl scroll-fade-left">
        <img src="{{ asset('images/booking.jpg') }}" alt="Service booking" class="h-full w-full object-cover" />
      </div>
      <div class="space-y-6 scroll-fade-right" style="transition-delay:.1s">
        <h2 class="text-3xl font-bold text-base-content sm:text-4xl">
          Service Details &amp; Booking
        </h2>
        <div class="grid gap-6 sm:grid-cols-2">
          <div class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm scroll-zoom-in">
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full border border-base-300 bg-base-100 text-base-content/70">
              <x-heroicon-o-user class="h-6 w-6" />
            </div>
            <h3 class="text-xl font-bold text-base-content">Provider Profile</h3>
            <p class="mt-2 text-base text-base-content/70">
              Learn about your chosen professional, their experience, and specialties.
            </p>
          </div>
          <div class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm scroll-zoom-in" style="transition-delay:.1s">
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full border border-base-300 bg-base-100 text-base-content/70">
              <x-heroicon-o-calendar-days class="h-6 w-6" />
            </div>
            <h3 class="text-xl font-bold text-base-content">Flexible Scheduling</h3>
            <p class="mt-2 text-base text-base-content/70">
              Select a date and time that fits your busy schedule.
            </p>
          </div>
        </div>
        <div class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm scroll-zoom-in" style="transition-delay:.2s">
          <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full border border-base-300 bg-base-100 text-base-content/70">
              <x-heroicon-o-lock-closed class="h-6 w-6" />
          </div>
          <h3 class="text-xl font-bold text-base-content">Transparent Pricing</h3>
          <p class="mt-2 text-base text-base-content/70">
            View clear, upfront costs before you commit.
          </p>
        </div>
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Book Now</a>
      </div>
    </div>
  </div>
</section>

<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content sm:text-4xl scroll-fade-up">
      Your Voice Matters: Reviews
    </h2>
    <div class="mt-10 grid gap-8 lg:grid-cols-2">
      <div class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm scroll-fade-up" style="transition-delay:.1s">
        <h3 class="text-2xl font-bold text-base-content">Ratings Snapshot</h3>
        <p class="mt-2 text-base text-base-content/70">
          See how providers are performing with a comprehensive overview of customer feedback.
        </p>
        <div class="mt-6 space-y-5">
          <div class="flex items-center gap-4">
            <div class="flex gap-1 text-warning">
              <x-heroicon-s-star class="h-5 w-5" />
              <x-heroicon-s-star class="h-5 w-5" />
              <x-heroicon-s-star class="h-5 w-5" />
              <x-heroicon-s-star class="h-5 w-5" />
              <x-heroicon-s-star class="h-5 w-5" />
            </div>
            <span class="text-lg font-bold text-base-content">4.8</span>
          </div>
          <div class="flex items-center gap-4">
            <div class="flex gap-1 text-warning">
              <x-heroicon-s-star class="h-5 w-5" />
              <x-heroicon-s-star class="h-5 w-5" />
              <x-heroicon-s-star class="h-5 w-5" />
              <x-heroicon-s-star class="h-5 w-5" />
              <x-heroicon-s-star class="h-5 w-5" />
            </div>
            <span class="text-lg font-bold text-base-content">5</span>
          </div>
          <div class="flex items-center gap-4">
            <div class="flex gap-1 text-warning">
              <x-heroicon-s-star class="h-5 w-5" />
              <x-heroicon-s-star class="h-5 w-5" />
              <x-heroicon-s-star class="h-5 w-5" />
              <x-heroicon-s-star class="h-5 w-5" />
              <x-heroicon-s-star class="h-5 w-5 text-base-300" />
            </div>
            <span class="text-lg font-bold text-base-content">4.5</span>
          </div>
        </div>
      </div>
      <div class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm scroll-fade-up" style="transition-delay:.2s">
        <h3 class="text-2xl font-bold text-base-content">Share Your Experience</h3>
        <p class="mt-2 text-base text-base-content/70">
          Help others make informed decisions by sharing your honest feedback.
        </p>
        <div class="mt-6 overflow-hidden rounded-2xl bg-base-200">
          <img src="{{ asset('images/ratings.jpg') }}" alt="Submit review"  class="h-full w-full object-cover" />
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content sm:text-4xl scroll-fade-up">
      How It Works: Seamless Service, Every Time
    </h2>
    <div class="mt-10 grid gap-8 lg:grid-cols-3">
      <div class="scroll-fade-up" style="transition-delay:.1s">
        <div class="rounded-full bg-base-200 px-4 py-3 text-center text-lg font-bold text-base-content/70">1</div>
        <h3 class="mt-4 text-xl font-bold text-base-content">Find Your Service</h3>
        <p class="mt-2 text-base text-base-content/70">
          Browse a wide array of categories or search for specific local
          professionals.
        </p>
      </div>
      <div class="scroll-fade-up" style="transition-delay:.2s">
        <div class="rounded-full bg-base-200 px-4 py-3 text-center text-lg font-bold text-base-content/70">2</div>
        <h3 class="mt-4 text-xl font-bold text-base-content">Book with Confidence</h3>
        <p class="mt-2 text-base text-base-content/70">
          Compare profiles, read reviews, and schedule services with transparent
          pricing.
        </p>
      </div>
      <div class="scroll-fade-up" style="transition-delay:.3s">
        <div class="rounded-full bg-base-200 px-4 py-3 text-center text-lg font-bold text-base-content/70">3</div>
        <h3 class="mt-4 text-xl font-bold text-base-content">Experience Excellence</h3>
        <p class="mt-2 text-base text-base-content/70">
          Enjoy high-quality service and leave feedback to support our community.
        </p>
      </div>
    </div>
  </div>
</section>

<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid items-center gap-10 lg:grid-cols-2">
      <div class="overflow-hidden rounded-3xl bg-base-100 shadow-xl scroll-fade-left">
        <img src="{{ asset('images/browse.jpg') }}" alt="Browse services"  class="h-full w-full object-cover" />
      </div>
      <div class="space-y-6 scroll-fade-right" style="transition-delay:.1s">
        <h2 class="text-3xl font-bold text-base-content sm:text-4xl">
          Browse Services: Find What You Need
        </h2>
        <div class="grid gap-6 sm:grid-cols-2">
          <div>
            <h3 class="text-xl font-bold text-base-content">Refine Your Search</h3>
            <p class="mt-2 text-base text-base-content/70">
              Use our intelligent filters to quickly pinpoint the perfect provider.
            </p>
           
          </div>
          <div>
            <h3 class="text-xl font-bold text-base-content">Discover Local Talent</h3>
            <p class="mt-2 text-base text-base-content/70">
              Explore detailed service cards, showcasing provider expertise and reviews.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection

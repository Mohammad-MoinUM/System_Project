@extends('layouts.app')

@section('hideNavbar', true)

@section('content')

<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid items-center gap-10 lg:grid-cols-2">
      <div class="space-y-6">
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
      <div class="overflow-hidden rounded-3xl bg-base-100 shadow-xl">
        <img src="{{ asset('images/getstarted.jpg') }}" alt="Local services" loading="lazy" class="h-full w-full object-cover" />
      </div>
    </div>
  </div>
</section>

<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid items-center gap-10 lg:grid-cols-2">
      <div class="overflow-hidden rounded-3xl bg-base-200 shadow-xl">
        <img src="{{ asset('images/booking.jpg') }}" alt="Service booking" loading="lazy" class="h-full w-full object-cover" />
      </div>
      <div class="space-y-6">
        <h2 class="text-3xl font-bold text-base-content sm:text-4xl">
          Service Details &amp; Booking
        </h2>
        <div class="grid gap-6 sm:grid-cols-2">
          <div class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm">
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full border border-base-300 bg-base-100 text-base-content/70">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-base-content">Provider Profile</h3>
            <p class="mt-2 text-base text-base-content/70">
              Learn about your chosen professional, their experience, and specialties.
            </p>
          </div>
          <div class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm">
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full border border-base-300 bg-base-100 text-base-content/70">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-base-content">Flexible Scheduling</h3>
            <p class="mt-2 text-base text-base-content/70">
              Select a date and time that fits your busy schedule.
            </p>
          </div>
        </div>
        <div class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm">
          <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full border border-base-300 bg-base-100 text-base-content/70">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V2m0 0a3 3 0 0 0-3 3v1m3-4a3 3 0 0 1 3 3v1m-9 5h12a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2z" />
            </svg>
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
    <h2 class="text-3xl font-bold text-base-content sm:text-4xl">
      Your Voice Matters: Reviews
    </h2>
    <div class="mt-10 grid gap-8 lg:grid-cols-2">
      <div class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm">
        <h3 class="text-2xl font-bold text-base-content">Ratings Snapshot</h3>
        <p class="mt-2 text-base text-base-content/70">
          See how providers are performing with a comprehensive overview of customer feedback.
        </p>
        <div class="mt-6 space-y-5">
          <div class="flex items-center gap-4">
            <div class="flex gap-1 text-warning">
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <span class="text-lg font-bold text-base-content">4.8</span>
          </div>
          <div class="flex items-center gap-4">
            <div class="flex gap-1 text-warning">
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <span class="text-lg font-bold text-base-content">5</span>
          </div>
          <div class="flex items-center gap-4">
            <div class="flex gap-1 text-warning">
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <svg class="h-5 w-5 text-base-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <span class="text-lg font-bold text-base-content">4.5</span>
          </div>
        </div>
      </div>
      <div class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm">
        <h3 class="text-2xl font-bold text-base-content">Share Your Experience</h3>
        <p class="mt-2 text-base text-base-content/70">
          Help others make informed decisions by sharing your honest feedback.
        </p>
        <div class="mt-6 overflow-hidden rounded-2xl bg-base-200">
          <img src="{{ asset('images/ratings.jpg') }}" alt="Submit review" loading="lazy" class="h-full w-full object-cover" />
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content sm:text-4xl">
      How It Works: Seamless Service, Every Time
    </h2>
    <div class="mt-10 grid gap-8 lg:grid-cols-3">
      <div>
        <div class="rounded-full bg-base-200 px-4 py-3 text-center text-lg font-bold text-base-content/70">1</div>
        <h3 class="mt-4 text-xl font-bold text-base-content">Find Your Service</h3>
        <p class="mt-2 text-base text-base-content/70">
          Browse a wide array of categories or search for specific local
          professionals.
        </p>
      </div>
      <div>
        <div class="rounded-full bg-base-200 px-4 py-3 text-center text-lg font-bold text-base-content/70">2</div>
        <h3 class="mt-4 text-xl font-bold text-base-content">Book with Confidence</h3>
        <p class="mt-2 text-base text-base-content/70">
          Compare profiles, read reviews, and schedule services with transparent
          pricing.
        </p>
      </div>
      <div>
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
      <div class="overflow-hidden rounded-3xl bg-base-100 shadow-xl">
        <img src="{{ asset('images/browse.jpg') }}" alt="Browse services" loading="lazy" class="h-full w-full object-cover" />
      </div>
      <div class="space-y-6">
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

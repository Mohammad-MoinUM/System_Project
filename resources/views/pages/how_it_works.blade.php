@extends('layouts.app')

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-primary/10 to-secondary/10">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 text-center">
    <h1 class="text-4xl font-bold text-base-content scroll-fade-up">How It Works</h1>
    <p class="mt-3 text-lg text-base-content/60 max-w-2xl mx-auto scroll-fade-up" style="transition-delay:.1s">Getting started with HaalChaal is simple. Follow these easy steps.</p>
  </div>
</section>

{{-- Steps --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid gap-12 lg:grid-cols-3">

      {{-- Step 1 --}}
      <div class="text-center scroll-fade-up" style="transition-delay:.1s">
        <div class="mx-auto mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-content text-2xl font-bold">1</div>
        <h3 class="text-xl font-bold text-base-content">Create Your Account</h3>
        <p class="mt-3 text-base-content/60">Sign up as a customer to find services, or as a provider to offer your skills. Complete your profile to get started.</p>
      </div>

      {{-- Step 2 --}}
      <div class="text-center scroll-fade-up" style="transition-delay:.2s">
        <div class="mx-auto mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-content text-2xl font-bold">2</div>
        <h3 class="text-xl font-bold text-base-content">Browse & Book</h3>
        <p class="mt-3 text-base-content/60">Search for the service you need, compare providers based on ratings and pricing, and book your preferred time slot.</p>
      </div>

      {{-- Step 3 --}}
      <div class="text-center scroll-fade-up" style="transition-delay:.3s">
        <div class="mx-auto mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-content text-2xl font-bold">3</div>
        <h3 class="text-xl font-bold text-base-content">Get It Done</h3>
        <p class="mt-3 text-base-content/60">The provider arrives at your scheduled time, completes the job, and you can rate the experience to help the community.</p>
      </div>
    </div>
  </div>
</section>

{{-- For Providers --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content text-center scroll-fade-up">For Providers</h2>
    <p class="mt-3 text-base-content/60 text-center max-w-xl mx-auto scroll-fade-up" style="transition-delay:.05s">Join our platform and grow your service business.</p>

    <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
      @php
        $providerSteps = [
          ['icon' => 'heroicon-o-user-plus', 'title' => 'Register', 'desc' => 'Create your provider account and complete your professional profile.'],
          ['icon' => 'heroicon-o-wrench-screwdriver', 'title' => 'List Services', 'desc' => 'Add the services you offer with pricing and descriptions.'],
          ['icon' => 'heroicon-o-bell-alert', 'title' => 'Receive Bookings', 'desc' => 'Get notified when customers book your services.'],
          ['icon' => 'heroicon-o-currency-dollar', 'title' => 'Earn Money', 'desc' => 'Complete jobs and grow your earnings and reputation.'],
        ];
      @endphp

      @foreach($providerSteps as $step)
        <div class="rounded-2xl bg-base-100 p-6 shadow-sm text-center scroll-fade-up" style="transition-delay:{{ ($loop->index + 1) * 0.1 }}s">
          <div class="mx-auto mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
            <x-dynamic-component :component="$step['icon']" class="h-6 w-6" />
          </div>
          <h3 class="text-lg font-bold text-base-content">{{ $step['title'] }}</h3>
          <p class="mt-2 text-sm text-base-content/60">{{ $step['desc'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- CTA --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 text-center">
    <h2 class="text-3xl font-bold text-base-content scroll-fade-up">Ready to Get Started?</h2>
    <p class="mt-3 text-base-content/60 scroll-fade-up" style="transition-delay:.05s">Join thousands of users already on HaalChaal.</p>
    <a href="{{ route('register') }}" class="btn btn-primary btn-lg mt-6 scroll-fade-up" style="transition-delay:.1s">Create Account</a>
  </div>
</section>

@endsection

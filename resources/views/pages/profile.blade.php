@extends('layouts.app')

@section('title', 'My Profile')
@section('content')

@php
    $isProvider = $user->role === 'provider';
    $currencyOptions = config('currencies.options', []);
    $currency = session('currency', config('currencies.default', 'BDT'));
    $currencyMeta = $currencyOptions[$currency] ?? ['symbol' => $currency, 'rate' => 1];
    $currencySymbol = $currencyMeta['symbol'] ?? $currency;
    $currencyRate = $currencyMeta['rate'] ?? 1;
@endphp

<section class="bg-base-100">
  <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">

    @if(session('success'))
      <div class="alert alert-success mb-6">
        <x-heroicon-o-check-circle class="w-5 h-5" />
        <span>{{ session('success') }}</span>
      </div>
    @endif

    {{-- ═══ Profile Header ═══ --}}
    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 mb-10">
      <div class="w-28 h-28 rounded-full overflow-hidden bg-base-300 flex-shrink-0 ring-4 {{ $isProvider ? 'ring-primary/20' : 'ring-secondary/20' }}">
        @if($user->photo)
          <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" class="w-full h-full object-cover" />
        @else
          <div class="w-full h-full flex items-center justify-center text-base-content/30">
            <x-heroicon-o-user class="w-14 h-14" />
          </div>
        @endif
      </div>
      <div class="text-center sm:text-left flex-1">
        <h2 class="text-2xl font-bold text-base-content">{{ $user->first_name }} {{ $user->last_name }}</h2>
        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-2">
          <span class="badge {{ $isProvider ? 'badge-primary' : 'badge-secondary' }} badge-sm">{{ $isProvider ? 'Service Provider' : 'Customer' }}</span>
          @if($isProvider && $user->expertise)
            <span class="badge badge-outline badge-sm">{{ $user->expertise }}</span>
          @endif
        </div>
        @if($isProvider && $user->bio)
          <p class="text-sm text-base-content/60 mt-3 max-w-lg">{{ $user->bio }}</p>
        @endif
        <div class="mt-4">
          <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">
            <x-heroicon-o-pencil-square class="w-4 h-4" />
            Edit Profile
          </a>
        </div>
      </div>
    </div>

    {{-- ═══ Stats Overview ═══ --}}
    @if($isProvider)
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-10">
        <div class="rounded-xl bg-primary/5 p-4 text-center">
          <p class="text-2xl font-black text-base-content">{{ $stats['jobs_completed'] }}</p>
          <p class="text-xs text-base-content/50 mt-1">Jobs Done</p>
        </div>
        <div class="rounded-xl bg-primary/5 p-4 text-center">
          <p class="text-2xl font-black text-base-content">{{ $stats['active_jobs'] }}</p>
          <p class="text-xs text-base-content/50 mt-1">Active Jobs</p>
        </div>
        <div class="rounded-xl bg-primary/5 p-4 text-center">
          <p class="text-2xl font-black text-base-content">{{ $currencySymbol }} {{ number_format($stats['total_earnings'] * $currencyRate, 0) }}</p>
          <p class="text-xs text-base-content/50 mt-1">Earnings</p>
        </div>
        <div class="rounded-xl bg-primary/5 p-4 text-center">
          <div class="flex items-center justify-center gap-1">
            <x-heroicon-s-star class="w-4 h-4 text-warning" />
            <p class="text-2xl font-black text-base-content">{{ $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : 'N/A' }}</p>
          </div>
          <p class="text-xs text-base-content/50 mt-1">Rating</p>
        </div>
        <div class="rounded-xl bg-primary/5 p-4 text-center">
          <p class="text-2xl font-black text-base-content">{{ $stats['total_reviews'] }}</p>
          <p class="text-xs text-base-content/50 mt-1">Reviews</p>
        </div>
        <div class="rounded-xl bg-primary/5 p-4 text-center">
          <p class="text-2xl font-black text-base-content">{{ $stats['services_count'] }}</p>
          <p class="text-xs text-base-content/50 mt-1">Services</p>
        </div>
      </div>
    @else
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-10">
        <div class="rounded-xl bg-secondary/5 p-4 text-center">
          <p class="text-2xl font-black text-base-content">{{ $stats['total_bookings'] }}</p>
          <p class="text-xs text-base-content/50 mt-1">Total Bookings</p>
        </div>
        <div class="rounded-xl bg-secondary/5 p-4 text-center">
          <p class="text-2xl font-black text-base-content">{{ $stats['active_bookings'] }}</p>
          <p class="text-xs text-base-content/50 mt-1">Active</p>
        </div>
        <div class="rounded-xl bg-secondary/5 p-4 text-center">
          <p class="text-2xl font-black text-base-content">{{ $currencySymbol }} {{ number_format($stats['total_spent'] * $currencyRate, 0) }}</p>
          <p class="text-xs text-base-content/50 mt-1">Total Spent</p>
        </div>
        <div class="rounded-xl bg-secondary/5 p-4 text-center">
          <p class="text-2xl font-black text-base-content">{{ $stats['reviews_given'] }}</p>
          <p class="text-xs text-base-content/50 mt-1">Reviews Given</p>
        </div>
        <div class="rounded-xl bg-secondary/5 p-4 text-center">
          <p class="text-2xl font-black text-base-content">{{ $stats['saved_providers'] }}</p>
          <p class="text-xs text-base-content/50 mt-1">Saved Providers</p>
        </div>
        <div class="rounded-xl bg-secondary/5 p-4 text-center">
          <p class="text-2xl font-black text-base-content">{{ $stats['loyalty_points'] }}</p>
          <p class="text-xs text-base-content/50 mt-1">Reward Points</p>
        </div>
      </div>
    @endif

    {{-- ═══ Information Cards ═══ --}}
    <div class="space-y-4">

      {{-- Contact --}}
      <div class="rounded-xl bg-base-200/50 p-5">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-base-content/40 mb-3">Contact Information</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <span class="text-xs text-base-content/50">Email</span>
            <p class="text-sm font-medium text-base-content">{{ $user->email }}</p>
          </div>
          <div>
            <span class="text-xs text-base-content/50">Phone</span>
            <p class="text-sm font-medium text-base-content">{{ $user->phone ?: 'Not set' }}</p>
          </div>
          @if($user->alt_phone)
            <div>
              <span class="text-xs text-base-content/50">Alt. Phone</span>
              <p class="text-sm font-medium text-base-content">{{ $user->alt_phone }}</p>
            </div>
          @endif
        </div>
      </div>

      {{-- Location --}}
      <div class="rounded-xl bg-base-200/50 p-5">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-base-content/40 mb-3">Location</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <span class="text-xs text-base-content/50">City</span>
            <p class="text-sm font-medium text-base-content">{{ $user->city ?: 'Not set' }}</p>
          </div>
          <div>
            <span class="text-xs text-base-content/50">Area</span>
            <p class="text-sm font-medium text-base-content">{{ $user->area ?: 'Not set' }}</p>
          </div>
        </div>
      </div>

      @unless($isProvider)
        <div class="rounded-xl bg-base-200/50 p-5">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-base-content/40 mb-3">Saved Preferences</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <span class="text-xs text-base-content/50">Preferred Time Slots</span>
              <div class="mt-2 flex flex-wrap gap-2">
                @forelse($user->preferred_time_slots ?? [] as $slot)
                  <span class="badge badge-outline">{{ ucfirst($slot) }}</span>
                @empty
                  <span class="text-sm text-base-content/60">No preferred slots saved</span>
                @endforelse
              </div>
            </div>
            <div>
              <span class="text-xs text-base-content/50">Provider Gender Preference</span>
              <p class="text-sm font-medium text-base-content mt-1">{{ $user->provider_gender_preference ? ucfirst($user->provider_gender_preference) : 'Any' }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-xl bg-base-200/50 p-5">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-base-content/40 mb-3">Rewards & Referrals</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <span class="text-xs text-base-content/50">Reward Points</span>
              <p class="text-sm font-medium text-base-content">{{ $stats['loyalty_points'] ?? 0 }} points</p>
            </div>
            <div>
              <span class="text-xs text-base-content/50">Referral Code</span>
              <p class="text-sm font-medium text-base-content font-mono tracking-wider">{{ $stats['referral_code'] ?? $user->referral_code }}</p>
            </div>
            <div>
              <span class="text-xs text-base-content/50">Successful Referrals</span>
              <p class="text-sm font-medium text-base-content">{{ $stats['successful_referrals'] ?? 0 }}</p>
            </div>
            <div>
              <span class="text-xs text-base-content/50">Referral Credits Earned</span>
              <p class="text-sm font-medium text-base-content">{{ $currencySymbol }} {{ number_format(($stats['referral_credits_earned'] ?? 0) * $currencyRate, 2) }}</p>
            </div>
            <div>
              <span class="text-xs text-base-content/50">Saved Addresses</span>
              <p class="text-sm font-medium text-base-content">{{ $stats['saved_addresses'] ?? 0 }}</p>
            </div>
          </div>
        </div>

        @if($addresses->isNotEmpty())
          <div class="rounded-xl bg-base-200/50 p-5">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-base-content/40 mb-3">Saved Addresses</h3>
            <div class="space-y-3">
              @foreach($addresses as $address)
                <div class="rounded-lg bg-base-100 p-4 border {{ $address->is_default ? 'border-primary/30' : 'border-base-200' }}">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <div class="flex items-center gap-2">
                        <p class="font-semibold text-base-content">{{ $address->label }}</p>
                        @if($address->is_default)
                          <span class="badge badge-primary badge-sm">Default</span>
                        @endif
                      </div>
                      <p class="text-sm text-base-content/70 mt-1">{{ $address->line1 }}</p>
                      @if($address->line2)
                        <p class="text-sm text-base-content/70">{{ $address->line2 }}</p>
                      @endif
                      <p class="text-sm text-base-content/60 mt-1">{{ collect([$address->area, $address->city, $address->postal_code])->filter()->implode(', ') }}</p>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif
      @endunless

      {{-- ═══ Provider-Specific Sections ═══ --}}
      @if($isProvider)
        <div class="rounded-xl bg-base-200/50 p-5">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-base-content/40 mb-3">Professional Details</h3>
          <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <span class="text-xs text-base-content/50">Expertise</span>
                <p class="text-sm font-medium text-base-content">{{ $user->expertise ?: 'Not set' }}</p>
              </div>
              <div>
                <span class="text-xs text-base-content/50">Experience</span>
                <p class="text-sm font-medium text-base-content">{{ $user->experience_years !== null ? $user->experience_years . ' years' : 'Not set' }}</p>
              </div>
              <div>
                <span class="text-xs text-base-content/50">Education</span>
                <p class="text-sm font-medium text-base-content">{{ $user->education ?: 'Not set' }}</p>
              </div>
            </div>
            @if($user->institution)
              <div>
                <span class="text-xs text-base-content/50">Institution</span>
                <p class="text-sm font-medium text-base-content">{{ $user->institution }}</p>
              </div>
            @endif
          </div>
        </div>

        @if($user->services_offered)
          <div class="rounded-xl bg-base-200/50 p-5">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-base-content/40 mb-3">Services Offered</h3>
            <div class="flex flex-wrap gap-2">
              @foreach($user->services_offered as $service)
                <span class="badge badge-primary badge-outline">{{ $service }}</span>
              @endforeach
            </div>
          </div>
        @endif

        @if($user->certifications)
          <div class="rounded-xl bg-base-200/50 p-5">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-base-content/40 mb-3">Certifications</h3>
            <div class="flex flex-wrap gap-2">
              @foreach($user->certifications as $cert)
                <span class="badge badge-accent badge-outline">{{ $cert }}</span>
              @endforeach
            </div>
          </div>
        @endif

        <div class="rounded-xl bg-base-200/50 p-5">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-base-content/40 mb-3">Rewards & Referrals</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <span class="text-xs text-base-content/50">Referral Code</span>
              <p class="text-sm font-medium text-base-content font-mono tracking-wider">{{ $stats['referral_code'] ?? $user->referral_code }}</p>
            </div>
            <div>
              <span class="text-xs text-base-content/50">Successful Referrals</span>
              <p class="text-sm font-medium text-base-content">{{ $stats['successful_referrals'] ?? 0 }}</p>
            </div>
            <div>
              <span class="text-xs text-base-content/50">Referral Credits Earned</span>
              <p class="text-sm font-medium text-base-content">{{ $currencySymbol }} {{ number_format(($stats['referral_credits_earned'] ?? 0) * $currencyRate, 2) }}</p>
            </div>
          </div>
        </div>

      {{-- ═══ Customer-Specific Sections ═══ --}}
      @else
        <div class="rounded-xl bg-base-200/50 p-5">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-base-content/40 mb-3">Account Details</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <span class="text-xs text-base-content/50">Member Since</span>
              <p class="text-sm font-medium text-base-content">{{ $user->created_at->format('F j, Y') }}</p>
            </div>
            <div>
              <span class="text-xs text-base-content/50">Onboarding</span>
              <p class="text-sm font-medium text-base-content">{{ $user->onboarding_completed ? 'Completed' : 'Incomplete' }}</p>
            </div>
          </div>
        </div>

        {{-- Quick Links for Customers --}}
        <div class="rounded-xl bg-base-200/50 p-5">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-base-content/40 mb-3">Quick Links</h3>
          <div class="flex flex-wrap gap-3">
            <a href="{{ route('customer.dashboard') }}" class="btn btn-outline btn-sm">
              <x-heroicon-o-squares-2x2 class="w-4 h-4" /> Dashboard
            </a>
            <a href="{{ route('customer.browse') }}" class="btn btn-outline btn-sm">
              <x-heroicon-o-magnifying-glass class="w-4 h-4" /> Browse Services
            </a>
            <a href="{{ route('customer.history') }}" class="btn btn-outline btn-sm">
              <x-heroicon-o-clock class="w-4 h-4" /> Booking History
            </a>
          </div>
        </div>
      @endif

    </div>

  </div>
</section>

@endsection

@extends('layouts.app')

@section('content')

<section class="bg-base-200">
  <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Provider Settings</h1>
    <p class="mt-2 text-base-content/60">Manage your provider account settings.</p>

    @if(session('success'))
      <div class="alert alert-success mt-4">{{ session('success') }}</div>
    @endif

    <div class="mt-8 space-y-6">
      {{-- Profile --}}
      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h2 class="text-xl font-bold text-base-content">Edit Profile</h2>
        <p class="mt-1 text-sm text-base-content/60">Update your personal and professional information.</p>
        <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm mt-4">Edit Profile</a>
      </div>

      {{-- Manage Services --}}
      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h2 class="text-xl font-bold text-base-content">My Services</h2>
        <p class="mt-1 text-sm text-base-content/60">Create, edit, or disable the services you offer.</p>
        <a href="{{ route('provider.services.index') }}" class="btn btn-outline btn-sm mt-4">Manage Services</a>
      </div>

      {{-- Service Areas --}}
      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h2 class="text-xl font-bold text-base-content">Service Areas</h2>
        <p class="mt-1 text-sm text-base-content/60">Define the zones where you are available for jobs.</p>
        <a href="{{ route('provider.service-areas.index') }}" class="btn btn-outline btn-sm mt-4">Manage Areas</a>
      </div>

      {{-- Payouts --}}
      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h2 class="text-xl font-bold text-base-content">Payouts</h2>
        <p class="mt-1 text-sm text-base-content/60">Withdraw your earnings to bKash, Nagad, or bank.</p>
        <a href="{{ route('provider.payouts.index') }}" class="btn btn-outline btn-sm mt-4">Manage Payouts</a>
      </div>

      {{-- Notifications --}}
      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h2 class="text-xl font-bold text-base-content">Notifications</h2>
        <p class="mt-1 text-sm text-base-content/60">View and manage your notifications.</p>
        <a href="{{ route('notifications.index') }}" class="btn btn-outline btn-sm mt-4">View Notifications</a>
      </div>

      {{-- Currency --}}
      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h2 class="text-xl font-bold text-base-content">Currency</h2>
        <p class="mt-1 text-sm text-base-content/60">Choose your preferred display currency.</p>
        <form method="POST" action="{{ route('currency.set') }}" class="mt-4">
          @csrf
          @php
            $currencyOptions = config('currencies.options', []);
            $currency = session('currency', config('currencies.default', 'BDT'));
          @endphp
          <select name="currency" onchange="this.form.submit()" class="select select-bordered select-sm">
            @foreach ($currencyOptions as $code => $meta)
              <option value="{{ $code }}" {{ $currency === $code ? 'selected' : '' }}>
                {{ $meta['symbol'] }} {{ $code }}
              </option>
            @endforeach
          </select>
        </form>
      </div>

      {{-- Account --}}
      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h2 class="text-xl font-bold text-base-content">Account</h2>
        <form method="POST" action="{{ route('logout') }}" class="mt-4">
          @csrf
          <button type="submit" class="btn btn-error btn-sm">Log Out</button>
        </form>
      </div>
    </div>
  </div>
</section>

@endsection

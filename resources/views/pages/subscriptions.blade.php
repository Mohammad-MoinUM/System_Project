@extends('layouts.app')

@section('content')
<section class="bg-base-200 min-h-screen">
  <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Subscription Plans</h1>
    <p class="mt-2 text-base-content/60">Unlock monthly discounts, priority slots, and premium support.</p>

    @if(session('success')) <div class="alert alert-success mt-4">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-error mt-4">{{ session('error') }}</div> @endif

    @if($activeSubscription)
      <div class="mt-6 rounded-2xl border border-success/40 bg-success/10 p-4 text-success-content">
        Active: {{ $activeSubscription->plan?->name }} until {{ $activeSubscription->ends_on?->format('d M Y') }}.
        <form method="POST" action="{{ route('subscriptions.cancel') }}" class="inline-block ml-3">
          @csrf
          <button class="btn btn-xs btn-outline">Cancel</button>
        </form>
      </div>
    @endif

    <div class="mt-6 grid gap-4 md:grid-cols-3">
      @forelse($plans as $plan)
        <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
          <h2 class="text-xl font-bold">{{ $plan->name }}</h2>
          <p class="mt-1 text-sm text-base-content/60">{{ $plan->description }}</p>
          <p class="mt-4 text-3xl font-extrabold text-primary">BDT {{ number_format((float) $plan->price, 2) }}</p>
          <ul class="mt-3 text-sm text-base-content/70 space-y-1">
            <li>{{ $plan->discount_percent }}% booking discount</li>
            <li>{{ $plan->monthly_service_limit }} services per month</li>
            <li>Priority support</li>
          </ul>

          <form method="POST" action="{{ route('subscriptions.subscribe', $plan) }}" class="mt-4">
            @csrf
            <label class="label"><span class="label-text">Duration</span></label>
            <select name="months" class="select select-bordered w-full">
              <option value="1">1 month</option>
              <option value="3">3 months</option>
              <option value="6">6 months</option>
              <option value="12">12 months</option>
            </select>
            <button class="btn btn-primary w-full mt-3">Subscribe</button>
          </form>
        </div>
      @empty
        <div class="rounded-2xl border border-dashed border-base-300 p-6 text-base-content/60">No subscription plans available yet.</div>
      @endforelse
    </div>
  </div>
</section>
@endsection

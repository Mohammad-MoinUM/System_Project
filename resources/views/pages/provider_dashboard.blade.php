@extends('layouts.app')

@section('content')

@php
    $currencyOptions = config('currencies.options', []);
    $currency = session('currency', config('currencies.default', 'BDT'));
    $currencyMeta = $currencyOptions[$currency] ?? ['symbol' => $currency, 'rate' => 1];
    $currencySymbol = $currencyMeta['symbol'] ?? $currency;
    $currencyRate = $currencyMeta['rate'] ?? 1;
  $providerLocationData = $providerLocation
    ? [
      'latitude' => (float) $providerLocation->latitude,
      'longitude' => (float) $providerLocation->longitude,
      'updated_at' => optional($providerLocation->updated_at)->toIso8601String(),
    ]
    : null;
  $activeBookingIds = \App\Models\Booking::where('provider_id', auth()->id())
    ->whereIn('status', ['active', 'in_progress'])
    ->pluck('id')
    ->values();
@endphp

@push('styles')
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

{{-- ═══════════════════ Greeting ═══════════════════ --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content scroll-fade-up">Hi, {{ auth()->user()->name }}!</h2>
    <p class="mt-2 text-base text-base-content/70 scroll-fade-up" style="transition-delay:.05s">Here's an overview of your provider activity. Keep up the great work!</p>
    <div class="mt-3 flex flex-wrap gap-2 scroll-fade-up" style="transition-delay:.08s">
      @foreach(auth()->user()->trustBadges() as $badge)
        <span class="badge badge-success badge-sm">{{ $badge }}</span>
      @endforeach
      @if(empty(auth()->user()->trustBadges()))
        <span class="badge badge-warning badge-sm">Trust verification pending</span>
      @endif
    </div>
  </div>
</section>

{{-- ═══════════════════ Dashboard Overview ═══════════════════ --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-bold text-base-content">Dashboard </h2>
        <p class="mt-2 text-base text-base-content/60">Quick insights into your HaalChaal performance.</p>
      </div>
      <form method="POST" action="{{ route('currency.set') }}">
        @csrf
        <select name="currency" onchange="this.form.submit()"
                class="select select-bordered select-sm">
          @foreach ($currencyOptions as $code => $meta)
            <option value="{{ $code }}" {{ $currency === $code ? 'selected' : '' }}>
              {{ $meta['symbol'] }} {{ $code }}
            </option>
          @endforeach
        </select>
      </form>
    </div>

    <div class="mt-6 rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h3 class="text-lg font-bold text-base-content">Quick Actions</h3>
          <p class="text-sm text-base-content/60">Use shortcuts to manage your business quickly.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <a href="{{ route('provider.jobs') }}" class="btn btn-primary btn-sm">Manage Jobs</a>
          <a href="{{ route('provider.schedule') }}" class="btn btn-outline btn-sm">Schedule</a>
          <a href="{{ route('provider.payouts.index') }}" class="btn btn-outline btn-sm">Payouts</a>
          <a href="{{ route('provider.service-areas.index') }}" class="btn btn-outline btn-sm">Service Areas</a>
          <a href="{{ route('provider.portfolio.index') }}" class="btn btn-outline btn-sm">Portfolio</a>
          <a href="{{ route('leaderboard.providers') }}" class="btn btn-outline btn-sm">Leaderboard</a>
          <a href="{{ route('provider.invoice.monthly', ['month' => now()->month, 'year' => now()->year]) }}" class="btn btn-outline btn-sm">Invoice PDF</a>
        </div>
      </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1.4fr_0.9fr]">
      <div class="overflow-hidden rounded-3xl border border-base-300 bg-base-100 shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-base-200 px-5 py-4">
          <div>
            <h3 class="text-lg font-bold text-base-content">Live Location</h3>
            <p class="text-sm text-base-content/60">Your GPS location updates automatically every 5 seconds.</p>
          </div>
          <span class="badge badge-success badge-outline" id="provider-location-status">Connecting</span>
        </div>
        <div id="provider-live-map" class="h-[380px] w-full bg-base-200"></div>
      </div>

      <div class="grid gap-4">
        <div class="rounded-3xl border border-base-300 bg-base-100 p-5 shadow-sm">
          <p class="text-xs uppercase text-base-content/50">Current Location</p>
          <p class="mt-2 text-2xl font-black text-base-content" id="provider-location-place">
            {{ $providerLocationData ? 'Resolving place...' : 'Waiting for GPS lock' }}
          </p>
          <p class="mt-1 text-sm text-base-content/70" id="provider-location-coordinates">
            {{ $providerLocationData ? number_format($providerLocationData['latitude'], 7) . ', ' . number_format($providerLocationData['longitude'], 7) : '' }}
          </p>
          <p class="mt-2 text-sm text-base-content/60" id="provider-location-updated">
            {{ $providerLocation && $providerLocation->updated_at ? 'Last synced ' . $providerLocation->updated_at->diffForHumans() : 'No location has been shared yet.' }}
          </p>
        </div>

        <div class="rounded-3xl border border-base-300 bg-base-100 p-5 shadow-sm">
          <p class="text-xs uppercase text-base-content/50">Live Tracking Notes</p>
          <ul class="mt-3 space-y-2 text-sm text-base-content/70">
            <li>• The marker moves as your device reports new coordinates.</li>
            <li>• Updates are sent to the server with fetch without reloading the page.</li>
            <li>• Keep location permission enabled in the browser for continuous tracking.</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
      {{-- Today's Earnings --}}
      <div class="rounded-2xl bg-primary/10 p-6 scroll-fade-up" style="transition-delay:.05s">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
          <x-heroicon-o-currency-dollar class="h-6 w-6" />
        </div>
        <h3 class="text-lg font-bold text-base-content">Today's Earnings</h3>
        <p class="mt-1 text-2xl font-black text-base-content" data-count-to="{{ ($stats['today_earnings'] ?? 0) * $currencyRate }}" data-count-prefix="{{ $currencySymbol }} " data-count-decimals="2">{{ $currencySymbol }} 0.00</p>
      </div>

      {{-- Jobs Completed --}}
      <div class="rounded-2xl bg-primary/10 p-6 scroll-fade-up" style="transition-delay:.1s">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
          <x-heroicon-o-check-badge class="h-6 w-6" />
        </div>
        <h3 class="text-lg font-bold text-base-content">Jobs Completed</h3>
        <p class="mt-1 text-2xl font-black text-base-content" data-count-to="{{ $stats['jobs_completed'] ?? 0 }}">0</p>
      </div>

      {{-- Avg. Rating --}}
      <div class="rounded-2xl bg-primary/10 p-6 scroll-fade-up" style="transition-delay:.15s">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
          <x-heroicon-s-star class="h-6 w-6" />
        </div>
        <h3 class="text-lg font-bold text-base-content">Avg. Rating</h3>
        <p class="mt-1 text-2xl font-black text-base-content"
          @if($stats['avg_rating'] !== null) data-count-to="{{ $stats['avg_rating'] }}" data-count-decimals="2" @endif
        >{{ $stats['avg_rating'] !== null ? '0.00' : 'N/A' }}</p>
      </div>

      {{-- Active Requests --}}
      <div class="rounded-2xl bg-primary/10 p-6 scroll-fade-up" style="transition-delay:.2s">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
          <x-heroicon-o-clock class="h-6 w-6" />
        </div>
        <h3 class="text-lg font-bold text-base-content">Active Requests</h3>
        <p class="mt-1 text-2xl font-black text-base-content" data-count-to="{{ $stats['active_requests'] ?? 0 }}">0</p>
      </div>

      {{-- Unread Booking Chats --}}
      <div class="rounded-2xl bg-primary/10 p-6 scroll-fade-up" style="transition-delay:.25s">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
          <x-heroicon-o-chat-bubble-left-right class="h-6 w-6" />
        </div>
        <h3 class="text-lg font-bold text-base-content">Unread Chats</h3>
        <p class="mt-1 text-2xl font-black text-base-content" data-count-to="{{ $unreadBookingChats ?? 0 }}">0</p>
      </div>

      {{-- Pending Payouts --}}
      <div class="rounded-2xl bg-primary/10 p-6 scroll-fade-up" style="transition-delay:.3s">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
          <x-heroicon-o-banknotes class="h-6 w-6" />
        </div>
        <h3 class="text-lg font-bold text-base-content">Pending Payouts</h3>
        <p class="mt-1 text-2xl font-black text-base-content" data-count-to="{{ $pendingPayoutCount ?? 0 }}">0</p>
        <p class="mt-1 text-xs text-base-content/60">{{ $currencySymbol }} {{ number_format(($pendingPayoutAmount ?? 0) * $currencyRate, 2) }}</p>
      </div>
    </div>
  </div>
</section>
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-base-content">Revenue Forecast</h2>
    <p class="mt-2 text-base-content/60">Projected month-end earnings based on your current completion pace.</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
      <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
        <p class="text-xs uppercase text-base-content/50">Daily Run Rate</p>
        <p class="mt-2 text-2xl font-black text-base-content">{{ $currencySymbol }} {{ number_format(($dailyRunRate ?? 0) * $currencyRate, 2) }}</p>
        <p class="mt-1 text-sm text-base-content/60">Average completed earnings per working day this month ({{ $elapsedWorkingDaysInMonth ?? 0 }} working days so far).</p>
      </div>

      <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
        <p class="text-xs uppercase text-base-content/50">Estimated Monthly Income</p>
        <p class="mt-2 text-2xl font-black text-primary">{{ $currencySymbol }} {{ number_format(($forecastMonthEarnings ?? 0) * $currencyRate, 2) }}</p>
        <p class="mt-1 text-sm text-base-content/60">Estimated using {{ $totalWorkingDaysInMonth ?? 0 }} scheduled working days this month.</p>
        <p class="mt-2 text-xs text-base-content/60">Income Rate (vs last month):
          @if($earningsDeltaPercent !== null)
            <span class="font-semibold {{ $earningsDeltaPercent >= 0 ? 'text-success' : 'text-warning' }}">{{ $earningsDeltaPercent >= 0 ? '+' : '' }}{{ $earningsDeltaPercent }}%</span>
          @else
            <span class="font-semibold">No baseline yet</span>
          @endif
        </p>
      </div>

      <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
        <div class="flex items-center justify-between">
          <p class="text-xs uppercase text-base-content/50">Forecast Confidence</p>
          <span class="badge badge-info">{{ $forecastConfidence ?? 0 }}%</span>
        </div>
        <progress class="progress progress-primary mt-3 w-full" value="{{ $forecastConfidence ?? 0 }}" max="100"></progress>
        <p class="mt-2 text-sm text-base-content/60">Confidence grows with more completed jobs this month.</p>
      </div>
    </div>
  </div>
</section>
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-base-content">Trust Level</h2>
    <p class="mt-2 text-base-content/60">Your reliability score and next trust milestones.</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
      <div class="rounded-2xl border border-base-300 bg-base-100 p-5 lg:col-span-1">
        <p class="text-xs uppercase text-base-content/50">Current Level</p>
        <h3 class="mt-2 text-xl font-black text-base-content">{{ $trustLevel ?? 'Starter Provider' }}</h3>
        <p class="mt-2 text-4xl font-black text-primary">{{ $trustScore ?? 0 }}</p>
        <p class="text-sm text-base-content/60">out of 100</p>
        <progress class="progress progress-success mt-4 w-full" value="{{ $trustScore ?? 0 }}" max="100"></progress>
      </div>

      <div class="rounded-2xl border border-base-300 bg-base-100 p-5 lg:col-span-2">
        <h3 class="text-lg font-bold text-base-content">Milestone Progress</h3>
        <div class="mt-4 space-y-4">
          @foreach(($trustMilestones ?? collect()) as $milestone)
            <div>
              <div class="flex items-center justify-between text-sm">
                <span>{{ $milestone['title'] }}</span>
                <span class="font-semibold">{{ $milestone['current'] }} / {{ $milestone['target'] }}</span>
              </div>
              <progress class="progress progress-success mt-2 w-full" value="{{ $milestone['percent'] }}" max="100"></progress>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-base-content">Booking Funnel</h2>
    <p class="mt-2 text-base-content/60">Track lifecycle conversion from requests to completed jobs.</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
      <div class="rounded-2xl border border-base-300 bg-base-100 p-5 lg:col-span-2">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
          <div class="rounded-xl bg-base-200 p-4 text-center">
            <p class="text-xs uppercase text-base-content/50">Pending</p>
            <p class="mt-1 text-2xl font-black text-base-content">{{ $bookingFunnel['pending'] ?? 0 }}</p>
          </div>
          <div class="rounded-xl bg-base-200 p-4 text-center">
            <p class="text-xs uppercase text-base-content/50">Active</p>
            <p class="mt-1 text-2xl font-black text-base-content">{{ $bookingFunnel['active'] ?? 0 }}</p>
          </div>
          <div class="rounded-xl bg-base-200 p-4 text-center">
            <p class="text-xs uppercase text-base-content/50">In Progress</p>
            <p class="mt-1 text-2xl font-black text-base-content">{{ $bookingFunnel['in_progress'] ?? 0 }}</p>
          </div>
          <div class="rounded-xl bg-base-200 p-4 text-center">
            <p class="text-xs uppercase text-base-content/50">Awaiting Confirmation</p>
            <p class="mt-1 text-2xl font-black text-warning">{{ $bookingFunnel['awaiting_confirmation'] ?? 0 }}</p>
          </div>
          <div class="rounded-xl bg-base-200 p-4 text-center">
            <p class="text-xs uppercase text-base-content/50">Completed</p>
            <p class="mt-1 text-2xl font-black text-success">{{ $bookingFunnel['completed'] ?? 0 }}</p>
          </div>
          <div class="rounded-xl bg-base-200 p-4 text-center">
            <p class="text-xs uppercase text-base-content/50">Cancelled</p>
            <p class="mt-1 text-2xl font-black text-warning">{{ $bookingFunnel['cancelled'] ?? 0 }}</p>
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
        <h3 class="text-lg font-bold text-base-content">Conversion Rates</h3>
        <div class="mt-4 space-y-3 text-sm">
          <div class="flex items-center justify-between">
            <span>Acceptance Rate</span>
            <span class="badge badge-info">{{ $bookingFunnelRates['acceptance'] ?? 'N/A' }}{{ isset($bookingFunnelRates['acceptance']) ? '%' : '' }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Completion Rate</span>
            <span class="badge badge-success">{{ $bookingFunnelRates['completion'] ?? 'N/A' }}{{ isset($bookingFunnelRates['completion']) ? '%' : '' }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Cancellation Rate</span>
            <span class="badge badge-warning">{{ $bookingFunnelRates['cancellation'] ?? 'N/A' }}{{ isset($bookingFunnelRates['cancellation']) ? '%' : '' }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ═══════════════════ Repeat Client Radar ═══════════════════ --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-base-content">Repeat Client Radar</h2>
    <p class="mt-2 text-base-content/60">Customers who book with you repeatedly and their average spend.</p>

    <div class="mt-6 overflow-x-auto rounded-2xl border border-base-300 bg-base-100">
      <table class="table w-full">
        <thead>
          <tr>
            <th>Customer</th>
            <th>Completed Jobs</th>
            <th>Average Spend</th>
            <th>Last Completed</th>
          </tr>
        </thead>
        <tbody>
          @forelse(($repeatClientRadar ?? collect()) as $client)
            <tr>
              <td class="font-semibold">{{ $client['name'] }}</td>
              <td>{{ $client['completed_jobs'] }}</td>
              <td>{{ $currencySymbol }} {{ number_format(($client['avg_order_value'] ?? 0) * $currencyRate, 2) }}</td>
              <td>{{ $client['last_completed_human'] }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-base-content/60">No repeat clients yet. Complete more jobs to unlock this radar.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>

@include('pages.provider.feature_lab')

{{-- ═══════════════════ Smart Demand Insights ═══════════════════ --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-base-content">Smart Demand Insights</h2>
    <p class="mt-2 text-base-content/60">AI-like demand intelligence based on your completed booking history.</p>

    @if(!($smartDemandInsights['has_history'] ?? false))
      <div class="mt-6 rounded-2xl border border-dashed border-base-300 p-6 text-base-content/60">
        Complete a few jobs first to unlock demand predictions and best-time suggestions.
      </div>
    @else
      <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
          <p class="text-xs uppercase text-base-content/50">Analyzed Bookings</p>
          <p class="mt-1 text-3xl font-black text-base-content">{{ $smartDemandInsights['total_analyzed'] }}</p>
          <p class="mt-2 text-sm text-base-content/60">Avg job value: {{ $currencySymbol }} {{ number_format(($smartDemandInsights['average_ticket'] ?? 0) * $currencyRate, 2) }}</p>
        </div>

        <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
          <h3 class="font-semibold text-base-content">Peak Hours</h3>
          <div class="mt-3 space-y-3">
            @foreach(($smartDemandInsights['top_hours'] ?? []) as $hour)
              <div>
                <div class="flex items-center justify-between text-sm">
                  <span>{{ $hour['label'] }}</span>
                  <span class="font-semibold">{{ $hour['count'] }} jobs</span>
                </div>
                <progress class="progress progress-primary w-full" value="{{ $hour['score'] }}" max="100"></progress>
              </div>
            @endforeach
          </div>
        </div>

        <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
          <h3 class="font-semibold text-base-content">Top Days</h3>
          <div class="mt-3 space-y-3">
            @foreach(($smartDemandInsights['top_days'] ?? []) as $day)
              <div>
                <div class="flex items-center justify-between text-sm">
                  <span>{{ $day['label'] }}</span>
                  <span class="font-semibold">{{ $day['count'] }} jobs</span>
                </div>
                <progress class="progress progress-success w-full" value="{{ $day['score'] }}" max="100"></progress>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      <div class="mt-6 rounded-2xl border border-base-300 bg-base-100 p-5">
        <h3 class="text-lg font-bold text-base-content">Recommended Slots (Next 2 Weeks)</h3>
        <p class="mt-1 text-sm text-base-content/60">Plan your availability where demand is historically strongest.</p>

        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
          @forelse(($smartDemandInsights['suggested_slots'] ?? []) as $slot)
            <div class="rounded-xl bg-base-200 p-4">
              <p class="font-semibold text-base-content">{{ $slot['day_label'] }}</p>
              <p class="text-sm text-base-content/70">{{ $slot['time_label'] }}</p>
              <div class="mt-3 flex items-center justify-between">
                <span class="badge badge-info">{{ $slot['confidence'] }}% confidence</span>
                <span class="text-sm font-semibold">{{ $currencySymbol }} {{ number_format($slot['projected_earning'] * $currencyRate, 2) }}</span>
              </div>
            </div>
          @empty
            <div class="rounded-xl border border-dashed border-base-300 p-4 text-base-content/60">
              Not enough time-distributed history yet for accurate slot predictions.
            </div>
          @endforelse
        </div>
      </div>
    @endif
  </div>
</section>

{{-- ═══════════════════ Growth Tips ═══════════════════ --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-base-content">Growth Tips</h2>
    <p class="mt-2 text-base-content/60">Actionable tips to improve rankings, earnings, and visibility.</p>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      @foreach($growthTips as $tip)
        <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
          <span class="badge badge-primary badge-sm">{{ $tip['badge'] }}</span>
          <h3 class="mt-3 text-lg font-bold text-base-content">{{ $tip['title'] }}</h3>
          <p class="mt-2 text-sm text-base-content/70">{{ $tip['description'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ═══════════════════ Earnings Insights ═══════════════════ --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-base-content">Earnings Insights</h2>
    <p class="mt-2 text-base-content/60">Understand your monthly trend and service-level performance.</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
      <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
        <h3 class="text-lg font-bold text-base-content">Monthly Earnings Trend</h3>
        @php $maxMonthly = max(1, (float) collect($monthlyEarningsTrend ?? [])->max('amount')); @endphp
        <div class="mt-4 space-y-3">
          @foreach(($monthlyEarningsTrend ?? collect()) as $point)
            <div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-base-content/70">{{ $point['label'] }}</span>
                <span class="font-semibold">{{ $currencySymbol }} {{ number_format($point['amount'] * $currencyRate, 2) }}</span>
              </div>
              <progress class="progress progress-primary w-full" value="{{ ($point['amount'] / $maxMonthly) * 100 }}" max="100"></progress>
            </div>
          @endforeach
        </div>
      </div>

      <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
        <h3 class="text-lg font-bold text-base-content">This Month vs Last Month</h3>
        <p class="mt-3 text-sm text-base-content/70">This month: <span class="font-semibold">{{ $currencySymbol }} {{ number_format(($currentMonthEarnings ?? 0) * $currencyRate, 2) }}</span></p>
        <p class="mt-1 text-sm text-base-content/70">Last month: <span class="font-semibold">{{ $currencySymbol }} {{ number_format(($lastMonthEarnings ?? 0) * $currencyRate, 2) }}</span></p>
        <p class="mt-2">
          @if($earningsDeltaPercent !== null)
            <span class="badge {{ $earningsDeltaPercent >= 0 ? 'badge-success' : 'badge-warning' }}">{{ $earningsDeltaPercent >= 0 ? '+' : '' }}{{ $earningsDeltaPercent }}%</span>
          @else
            <span class="badge badge-ghost">No baseline yet</span>
          @endif
        </p>

        <h4 class="mt-5 text-sm font-semibold uppercase text-base-content/60">Top Revenue Services</h4>
        <div class="mt-3 space-y-2">
          @forelse($servicePerformance as $service)
            <div class="flex items-center justify-between text-sm">
              <span>{{ $service->name }}</span>
              <span class="font-semibold">{{ $currencySymbol }} {{ number_format((float) $service->revenue * $currencyRate, 2) }}</span>
            </div>
          @empty
            <p class="text-sm text-base-content/50">No completed service data yet.</p>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ═══════════════════ Provider Missions ═══════════════════ --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-base-content">Provider Missions</h2>
    <p class="mt-2 text-base-content/60">Hit mission targets to unlock visibility and trust boosts.</p>

    <div class="mt-6 grid gap-4 md:grid-cols-3">
      @foreach($growthMissions as $mission)
        <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
          <h3 class="font-semibold text-base-content">{{ $mission['title'] }}</h3>
          <p class="mt-1 text-sm text-base-content/60">{{ $mission['current'] }} / {{ $mission['target'] }}</p>
          <progress class="progress progress-success mt-3 w-full" value="{{ $mission['percent'] }}" max="100"></progress>
          <p class="mt-2 text-xs text-base-content/60">Reward: {{ $mission['reward'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>


<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content">Recent Jobs</h2>
    <p class="mt-2 text-base text-base-content/60">Your latest service requests and their current status.</p>

    <div class="mt-8 overflow-x-auto">
      <table class="table w-full">
        <thead>
          <tr class="bg-base-300">
            <th class="text-base font-bold">Service</th>
            <th class="text-base font-bold">Notes</th>
            <th class="text-base font-bold">Time</th>
            <th class="text-base font-bold">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($recentJobs as $job)
            <tr class="hover">
              <td class="font-medium">{{ $job['title'] }}</td>
              <td class="text-base-content/60">{{ $job['description'] ?: '—' }}</td>
              <td class="text-base-content/60">{{ $job['time'] }}</td>
              <td>
                <span class="badge {{ $job['badge_class'] }} badge-outline uppercase text-xs font-semibold">{{ $job['status_label'] }}</span>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-base-content/50">No jobs yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-4">
      <a href="{{ route('provider.jobs') }}" class="btn btn-outline btn-primary btn-sm">View All Jobs</a>
    </div>
  </div>
</section>

{{-- ═══════════════════ Performance ═══════════════════ --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <span class="badge badge-ghost text-xs font-semibold uppercase">Metrics</span>
    <h2 class="mt-2 text-3xl font-bold text-base-content">Performance</h2>
    <p class="mt-2 text-base text-base-content/60">Track how well you're doing across key areas.</p>

    <div class="mt-8 grid gap-8 lg:grid-cols-3">
      @php
        $perfItems = [
          ['label' => 'Response Rate',   'value' => $performance['response_rate'],   'color' => 'progress-warning'],
          ['label' => 'Completion Rate', 'value' => $performance['completion_rate'], 'color' => 'progress-primary'],
          ['label' => 'On-time Arrival', 'value' => $performance['on_time_arrival'], 'color' => 'progress-success'],
        ];
      @endphp
      @foreach($perfItems as $item)
        <div class="rounded-2xl bg-base-200 p-6 scroll-fade-up" style="transition-delay:{{ $loop->index * 0.1 }}s">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-bold text-base-content">{{ $item['label'] }}</h3>
            <span class="text-2xl font-black text-base-content">
              {{ $item['value'] !== null ? $item['value'] . '%' : 'N/A' }}
            </span>
          </div>
          <progress class="progress {{ $item['color'] }} w-full h-3 animate-progress" value="{{ $item['value'] ?? 0 }}" max="100"></progress>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ═══════════════════ Customer Reviews ═══════════════════ --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content">Customer Reviews</h2>
    <p class="mt-2 text-base text-base-content/60">See what your customers are saying about your services.</p>

    <div class="mt-8 space-y-6">
      @forelse ($reviews as $review)
        <div class="border-l-4 border-primary pl-6 scroll-fade-left" style="transition-delay:{{ $loop->index * 0.1 }}s">
          <p class="text-base text-base-content/80 italic">"{{ $review['text'] ?: 'No comment provided.' }}"</p>
          <div class="mt-2 flex items-center gap-2 text-sm text-base-content/60">
            <div class="flex gap-0.5">
              @for ($i = 1; $i <= 5; $i++)
                <x-heroicon-s-star class="h-4 w-4 {{ $i <= ($review['rating'] ?? 0) ? 'text-warning' : 'text-base-300' }}" />
              @endfor
            </div>
            <span class="font-semibold">{{ $review['rating'] }}.0</span>
            <span>- {{ $review['author'] }}</span>
            <span class="text-base-content/40">{{ $review['time'] }}</span>
          </div>
        </div>
      @empty
        <p class="text-base-content/50">No reviews yet.</p>
      @endforelse
    </div>

    <div class="mt-6">
      <a href="{{ route('provider.reviews') }}" class="btn btn-outline btn-primary btn-sm">View All Reviews</a>
    </div>
  </div>
</section>
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <span class="badge badge-ghost text-xs font-semibold uppercase">Resources</span>
    <h2 class="mt-2 text-3xl font-bold text-base-content">Support &amp; Quick Stats</h2>
    <p class="mt-2 text-base text-base-content/60">Stay informed and get the help you need.</p>

    <div class="mt-8 grid items-start gap-10 lg:grid-cols-2">
      {{-- Left: Support Image --}}
      <div class="overflow-hidden rounded-2xl bg-base-100 shadow-xl scroll-fade-left">
        <img src="{{ asset('images/support.png') }}" alt="Support" loading="lazy" class="w-full" />
      </div>

      {{-- Right: Sidebar cards --}}
      <div class="space-y-6 scroll-fade-right" style="transition-delay:.15s">
        {{-- Quick Stats --}}
        <div class="rounded-2xl bg-warning/10 p-6">
          <h3 class="text-xl font-bold text-base-content">Quick Stats</h3>
          <div class="mt-4 grid grid-cols-2 gap-4">
            <div class="rounded-xl bg-base-100 p-4 text-center shadow-sm">
              <x-heroicon-o-arrow-trending-up class="mx-auto mb-2 h-5 w-5 text-success" />
              <p class="text-xl font-black text-base-content">{{ $currencySymbol }} {{ number_format(($quickStats['week_earnings'] ?? 0) * $currencyRate, 2) }}</p>
              <p class="mt-1 text-xs text-base-content/50">Last 7 days</p>
            </div>
            <div class="rounded-xl bg-base-100 p-4 text-center shadow-sm">
              <x-heroicon-o-user-group class="mx-auto mb-2 h-5 w-5 text-info" />
              <p class="text-xl font-black text-base-content">{{ $quickStats['clients_count'] ?? 0 }}</p>
              <p class="mt-1 text-xs text-base-content/50">Clients Served</p>
            </div>
          </div>
        </div>

        {{-- Schedule --}}
        <div class="rounded-2xl border border-base-300 bg-base-100 p-6">
          <h3 class="text-xl font-bold text-base-content">Your Schedule</h3>
          <p class="mt-1 text-sm text-base-content/60">Manage your upcoming appointments and availability.</p>
          <a href="{{ route('provider.schedule') }}" class="btn btn-outline btn-sm mt-4">View Schedule</a>
        </div>

        {{-- Support & Help --}}
        <div class="rounded-2xl border border-base-300 bg-base-100 p-6">
          <h3 class="text-xl font-bold text-base-content">Support &amp; Help</h3>
          <p class="mt-1 text-sm text-base-content/60">Find answers or contact us directly.</p>
          @if(($unreadSupportReplies ?? 0) > 0)
            <div class="mt-3">
              <span class="badge badge-error">{{ $unreadSupportReplies }} unread {{ $unreadSupportReplies === 1 ? 'reply' : 'replies' }}</span>
            </div>
          @endif
          @if(($serviceAreaCount ?? 0) > 0)
            <div class="mt-3">
              <span class="badge badge-info">{{ $serviceAreaCount }} active service {{ $serviceAreaCount === 1 ? 'area' : 'areas' }}</span>
            </div>
          @endif
          <a href="{{ route('home') }}" class="btn btn-primary btn-sm mt-4">Get Help</a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ═══════════════════ CTA Footer ═══════════════════ --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-black text-base-content  scroll-fade-up">Grow Your Business</h2>
    <p class="mt-2 text-base text-base-content/70 scroll-fade-up" style="transition-delay:.05s">Deliver exceptional service and watch your reputation soar on HaalChaal.</p>
    <a href="{{ route('provider.jobs') }}" class="btn btn-primary btn-lg mt-6 scroll-fade-up" style="transition-delay:.1s">View Open Jobs</a>
  </div>
</section>

@endsection

@push('scripts')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const initialLocation = @json($providerLocationData);
      const latestLocationUrl = @json(route('provider.location.show'));
      const updateLocationUrl = @json(route('provider.location.update'));
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      const mapContainer = document.getElementById('provider-live-map');
      const placeElement = document.getElementById('provider-location-place');
      const coordinatesElement = document.getElementById('provider-location-coordinates');
      const updatedElement = document.getElementById('provider-location-updated');
      const statusElement = document.getElementById('provider-location-status');
      const fallbackLocation = { latitude: 23.8103, longitude: 90.4125 };

      if (!mapContainer || !window.L) {
        if (statusElement) {
          statusElement.textContent = 'Map unavailable';
        }
        return;
      }

      let map = null;
      let marker = null;
      let latestKnownLocation = initialLocation || null;
      let lastSentAt = 0;
      let geocodeRequestVersion = 0;
      let popupRequestVersion = 0;
      let hasOpenedPopup = false;
      const placeCache = new Map();

      function formatCoordinates(latitude, longitude) {
        return `${Number(latitude).toFixed(7)}, ${Number(longitude).toFixed(7)}`;
      }

      async function getPlaceName(lat, lng) {
        const cacheKey = `${lat},${lng}`;
        if (placeCache.has(cacheKey)) {
          return placeCache.get(cacheKey);
        }

        try {
          const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`,
            { headers: { 'Accept-Language': 'en' } }
          );

          if (!response.ok) {
            throw new Error('Failed to resolve place name.');
          }

          const data = await response.json();
          const city = data.address?.city || data.address?.town || data.address?.village || '';
          const country = data.address?.country || '';
          const short = city && country ? `${city}, ${country}` : (data.display_name || `${lat}, ${lng}`);
          const place = {
            full: data.display_name || `${lat}, ${lng}`,
            short,
          };

          placeCache.set(cacheKey, place);
          return place;
        } catch (error) {
          console.error(error);
          return {
            full: `${lat}, ${lng}`,
            short: `${lat}, ${lng}`,
          };
        }
      }

      function setStatus(text, tone = 'success') {
        if (!statusElement) {
          return;
        }

        statusElement.textContent = text;
        statusElement.className = `badge badge-${tone} badge-outline`;
      }

      async function renderLocationText(location) {
        if (!location || typeof location.latitude === 'undefined' || typeof location.longitude === 'undefined') {
          return;
        }

        const lat = Number(location.latitude).toFixed(7);
        const lng = Number(location.longitude).toFixed(7);

        if (coordinatesElement) {
          coordinatesElement.textContent = `${lat}, ${lng}`;
        }

        if (placeElement) {
          placeElement.textContent = 'Resolving place...';
        }

        const requestVersion = ++geocodeRequestVersion;
        const placeName = await getPlaceName(lat, lng);
        if (requestVersion !== geocodeRequestVersion) {
          return;
        }

        if (placeElement) {
          placeElement.textContent = `Location: ${placeName.full}`;
        }
      }

      async function updateMarkerPopup(location) {
        if (!marker || !location || typeof location.latitude === 'undefined' || typeof location.longitude === 'undefined') {
          return;
        }

        const lat = Number(location.latitude).toFixed(7);
        const lng = Number(location.longitude).toFixed(7);
        const requestVersion = ++popupRequestVersion;
        const place = await getPlaceName(lat, lng);
        if (requestVersion !== popupRequestVersion) {
          return;
        }

        marker.bindPopup(`
          <strong>📍 ${place.short}</strong><br>
          <small>${lat}, ${lng}</small>
        `);

        if (!hasOpenedPopup) {
          marker.openPopup();
          hasOpenedPopup = true;
        }
      }

      function ensureMap(latitude, longitude) {
        if (!map) {
          map = L.map('provider-live-map', {
            zoomControl: true,
          }).setView([latitude, longitude], 15);

          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
          }).addTo(map);

          marker = L.marker([latitude, longitude], { draggable: false }).addTo(map);
          return;
        }

        marker.setLatLng([latitude, longitude]);
        map.panTo([latitude, longitude], { animate: true });
      }

      function applyLocation(location, updatedAt = null, quiet = false) {
        if (!location || typeof location.latitude === 'undefined' || typeof location.longitude === 'undefined') {
          return;
        }

        latestKnownLocation = location;
        ensureMap(location.latitude, location.longitude);
        renderLocationText(location);
        updateMarkerPopup(location);

        if (updatedElement && updatedAt) {
          updatedElement.textContent = `Last synced ${updatedAt}`;
        } else if (updatedElement && !quiet) {
          updatedElement.textContent = 'Location synced just now.';
        }

        if (statusElement && !quiet) {
          setStatus('Live', 'success');
        }
      }

      async function fetchLatestLocation() {
        try {
          const response = await fetch(latestLocationUrl, {
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
          });

          if (!response.ok) {
            throw new Error('Unable to load saved location.');
          }

          const payload = await response.json();
          if (payload.location) {
            applyLocation(payload.location, payload.location.updated_at, true);
            setStatus('Synced', 'info');
            return;
          }

          ensureMap(fallbackLocation.latitude, fallbackLocation.longitude);
          if (statusElement) {
            setStatus('Waiting', 'warning');
          }
        } catch (error) {
          console.error(error);
          ensureMap(fallbackLocation.latitude, fallbackLocation.longitude);
          setStatus('Offline', 'warning');
        }
      }

      async function sendLocation(location) {
        if (!location || !csrfToken) {
          return;
        }

        const now = Date.now();
        if (now - lastSentAt < 4500) {
          return;
        }

        lastSentAt = now;

        try {
          const response = await fetch(updateLocationUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
              latitude: Number(location.latitude).toFixed(7),
              longitude: Number(location.longitude).toFixed(7),
            }),
          });

          if (!response.ok) {
            throw new Error('Failed to update provider location.');
          }

          const payload = await response.json();
          if (payload.location) {
            applyLocation(payload.location, 'just now', false);
          }
        } catch (error) {
          console.error(error);
          setStatus('Sync error', 'error');
        }
      }

      function startWatching() {
        if (!navigator.geolocation) {
          setStatus('Geolocation unsupported', 'error');
          return;
        }

        navigator.geolocation.watchPosition(
          function (position) {
            const location = {
              latitude: position.coords.latitude,
              longitude: position.coords.longitude,
            };

            applyLocation(location, 'just now', false);
          },
          function () {
            setStatus('Permission needed', 'warning');
          },
          {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 10000,
          }
        );

        setInterval(function () {
          if (!latestKnownLocation) {
            return;
          }

          sendLocation(latestKnownLocation);
        }, 5000);
      }

      // Add Reverb WebSocket listener for active bookings.
      const reverbKey = '{{ env("REVERB_APP_KEY") }}';
      const reverbHost = '{{ env("REVERB_HOST", "localhost") }}';
      const reverbPort = {{ env("REVERB_PORT", 9090) }};
      const activeBookingIds = @json($activeBookingIds);

      function subscribeToBookingChannels() {
        if (!Array.isArray(activeBookingIds) || activeBookingIds.length === 0 || !window.Echo) {
          return;
        }

        activeBookingIds.forEach(function (bookingId) {
          window.Echo.channel(`booking.${bookingId}`)
            .listen('.provider.location.updated', function (data) {
              applyLocation({
                latitude: data.latitude,
                longitude: data.longitude,
              }, 'just now', false);
            });
        });
      }

      if (typeof window.Echo === 'undefined' && reverbKey) {
        const script = document.createElement('script');
        script.src = '/vendor/laravel/reverb/resources/js/echo.js';
        script.onload = function () {
          subscribeToBookingChannels();
        };
        document.head.appendChild(script);
      } else {
        subscribeToBookingChannels();
      }

      (async function initLiveTracking() {
        await fetchLatestLocation();
        startWatching();
      })();
    });
  </script>
@endpush
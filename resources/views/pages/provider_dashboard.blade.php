@extends('layouts.app')

@section('content')

@php
    $currencyOptions = config('currencies.options', []);
    $currency = session('currency', config('currencies.default', 'BDT'));
    $currencyMeta = $currencyOptions[$currency] ?? ['symbol' => $currency, 'rate' => 1];
    $currencySymbol = $currencyMeta['symbol'] ?? $currency;
    $currencyRate = $currencyMeta['rate'] ?? 1;
@endphp

{{-- ═══════════════════ Greeting ═══════════════════ --}}
<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-base-content scroll-fade-up">Hi, {{ auth()->user()->name }}!</h2>
    <p class="mt-2 text-base text-base-content/70 scroll-fade-up" style="transition-delay:.05s">Here's an overview of your provider activity. Keep up the great work!</p>
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

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
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
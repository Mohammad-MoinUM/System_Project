@extends('layouts.app')

@section('content')
<section class="bg-base-200 min-h-screen">
  <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Provider Leaderboard</h1>
    <p class="mt-2 text-base-content/60">Top-performing providers by completed jobs and monthly consistency.</p>

    <div class="mt-6 rounded-2xl border border-base-300 bg-base-100 p-5">
      <h2 class="text-lg font-bold">Monthly Top 5</h2>
      <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        @forelse($monthlyWinners as $winner)
          <div class="rounded-xl bg-base-200 p-3 text-center">
            <p class="font-semibold">{{ $winner->provider?->first_name }} {{ $winner->provider?->last_name }}</p>
            <p class="text-sm text-base-content/60">{{ $winner->completed_jobs }} jobs</p>
          </div>
        @empty
          <p class="text-base-content/60">No completed jobs this month yet.</p>
        @endforelse
      </div>
    </div>

    <div class="mt-6 overflow-x-auto rounded-2xl border border-base-300 bg-base-100">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Provider</th>
            <th>Completed Jobs</th>
            <th>Gross Earnings</th>
            <th>Avg Job Value</th>
            <th>Trust</th>
          </tr>
        </thead>
        <tbody>
          @forelse($topProviders as $index => $provider)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $provider->provider?->first_name }} {{ $provider->provider?->last_name }}</td>
              <td>{{ $provider->completed_jobs }}</td>
              <td>BDT {{ number_format((float) $provider->gross_earnings, 2) }}</td>
              <td>BDT {{ number_format((float) $provider->average_job_value, 2) }}</td>
              <td>
                @if(($provider->provider?->verification_status ?? null) === 'approved')
                  <span class="badge badge-success badge-sm">Verified</span>
                @endif
                @if(($provider->provider?->skill_verification_status ?? null) === 'verified')
                  <span class="badge badge-info badge-sm">Skills Checked</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-base-content/60">No leaderboard data yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>
@endsection

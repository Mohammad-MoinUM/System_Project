@extends('layouts.app')

@section('content')
<section class="bg-base-200">
  <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Provider Leaderboard</h1>
    <p class="mt-2 text-base-content/60">Top performers are ranked by completed jobs and customer ratings.</p>

    <div class="mt-8 overflow-x-auto rounded-2xl border border-base-300 bg-base-100">
      <table class="table w-full">
        <thead>
          <tr>
            <th>#</th>
            <th>Provider</th>
            <th>Location</th>
            <th>Completed Jobs</th>
            <th>Rating</th>
            <th>Badge</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rankedProviders as $provider)
            <tr>
              <td class="font-bold">{{ $provider['rank'] }}</td>
              <td>{{ $provider['name'] }}</td>
              <td>{{ trim(($provider['city'] ?? '') . ' ' . ($provider['area'] ?? '')) ?: 'N/A' }}</td>
              <td>{{ $provider['completed_jobs'] }}</td>
              <td>{{ $provider['avg_rating'] !== null ? number_format($provider['avg_rating'], 2) : 'N/A' }}</td>
              <td>
                <span class="badge {{ $provider['rank'] <= 3 ? 'badge-warning' : 'badge-info' }}">{{ $provider['badge'] }}</span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-base-content/50">No ranked providers yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>
@endsection

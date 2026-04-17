@extends('layouts.app')

@section('content')
<section class="bg-base-200 min-h-screen">
  <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Saved Services</h1>
    <p class="mt-2 text-base-content/60">Your wishlist of services to book later.</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      @forelse($savedServices as $saved)
        <div class="rounded-2xl border border-base-300 bg-base-100 p-4">
          <p class="font-semibold">{{ $saved->service?->name }}</p>
          <p class="text-sm text-base-content/60">{{ $saved->service?->category }}</p>
          <p class="text-sm mt-1">Provider: {{ $saved->service?->provider?->first_name }} {{ $saved->service?->provider?->last_name }}</p>
          <p class="text-primary font-bold mt-2">BDT {{ number_format((float) ($saved->service?->price ?? 0), 2) }}</p>
          <div class="mt-3 flex gap-2">
            @if($saved->service)
              <a href="{{ route('booking.create', $saved->service) }}" class="btn btn-primary btn-sm">Book Now</a>
              <form method="POST" action="{{ route('saved-services.toggle', $saved->service) }}">
                @csrf
                <button class="btn btn-outline btn-sm">Remove</button>
              </form>
            @endif
          </div>
        </div>
      @empty
        <div class="rounded-2xl border border-dashed border-base-300 p-8 text-base-content/60">
          No saved services yet.
        </div>
      @endforelse
    </div>

    <div class="mt-6">{{ $savedServices->links() }}</div>
  </div>
</section>
@endsection

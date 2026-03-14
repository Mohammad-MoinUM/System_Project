@extends('layouts.app')

@section('content')

<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Saved Providers</h1>
    <p class="mt-2 text-base-content/60">Your favorite service providers, all in one place.</p>
  </div>
</section>

<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    @if(session('success'))
      <div class="alert alert-success mb-6">{{ session('success') }}</div>
    @endif

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      @forelse($savedProviders as $saved)
        @php $provider = $saved->provider; @endphp
        @if($provider)
          <div class="card bg-base-100 border border-base-200 shadow-sm">
            <div class="card-body">
              <div class="flex items-start gap-4">
                @if($provider->photo)
                  <img src="{{ asset('storage/' . $provider->photo) }}" alt="" class="w-14 h-14 rounded-full object-cover" />
                @else
                  <div class="w-14 h-14 rounded-full bg-base-300 flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-user class="w-7 h-7 text-base-content/20" />
                  </div>
                @endif
                <div class="flex-1 min-w-0">
                  <h3 class="text-lg font-bold text-base-content truncate">{{ $provider->first_name }} {{ $provider->last_name }}</h3>
                  @if($provider->expertise)
                    <p class="text-sm text-base-content/60">{{ $provider->expertise }}</p>
                  @endif
                  @if($provider->city || $provider->area)
                    <p class="text-xs text-base-content/40 flex items-center gap-1 mt-1">
                      <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                      {{ collect([$provider->area, $provider->city])->filter()->implode(', ') }}
                    </p>
                  @endif
                </div>
              </div>

              @if($provider->bio)
                <p class="text-sm text-base-content/60 mt-3">{{ Str::limit($provider->bio, 100) }}</p>
              @endif

              <div class="card-actions justify-between items-center mt-4 pt-3 border-t border-base-200">
                <form method="POST" action="{{ route('customer.saved.destroy', $provider) }}">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-ghost btn-sm text-error">
                    <x-heroicon-s-heart class="w-4 h-4" />
                    Unsave
                  </button>
                </form>
                <a href="{{ route('customer.browse') }}" class="btn btn-primary btn-sm">View Services</a>
              </div>
            </div>
          </div>
        @endif
      @empty
        <div class="col-span-full text-center py-16">
          <x-heroicon-o-heart class="w-12 h-12 text-base-content/20 mx-auto" />
          <p class="mt-4 text-lg text-base-content/50">No saved providers yet.</p>
          <p class="text-sm text-base-content/40 mt-1">Browse services and save providers you like.</p>
          <a href="{{ route('customer.browse') }}" class="btn btn-primary btn-sm mt-4">Browse Services</a>
        </div>
      @endforelse
    </div>
  </div>
</section>

@endsection

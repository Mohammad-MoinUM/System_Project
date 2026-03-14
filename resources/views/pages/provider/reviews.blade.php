@extends('layouts.app')

@section('content')

<section class="bg-base-200">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Customer Reviews</h1>
    <p class="mt-2 text-base-content/60">See what your customers are saying.</p>

    {{-- Summary --}}
    <div class="mt-6 flex flex-wrap items-center gap-6">
      <div class="flex items-center gap-2">
        <x-heroicon-s-star class="w-8 h-8 text-warning" />
        <span class="text-3xl font-black text-base-content">{{ $avgRating ? number_format($avgRating, 1) : 'N/A' }}</span>
      </div>
      <span class="text-sm text-base-content/60">{{ $totalReviews }} {{ Str::plural('review', $totalReviews) }} total</span>

      {{-- Rating bars --}}
      <div class="flex-1 max-w-xs">
        @for($star = 5; $star >= 1; $star--)
          @php $count = $ratingDistribution[$star] ?? 0; $pct = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0; @endphp
          <div class="flex items-center gap-2 text-sm">
            <span class="w-3 text-base-content/60">{{ $star }}</span>
            <x-heroicon-s-star class="w-3.5 h-3.5 text-warning" />
            <progress class="progress progress-warning w-full h-2" value="{{ $pct }}" max="100"></progress>
            <span class="w-8 text-right text-base-content/40">{{ $count }}</span>
          </div>
        @endfor
      </div>
    </div>
  </div>
</section>

<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    @if(session('success'))
      <div class="alert alert-success mb-6">{{ session('success') }}</div>
    @endif

    <div class="space-y-6">
      @forelse($reviews as $review)
        <div class="rounded-2xl border border-base-200 p-6">
          <div class="flex items-start gap-4">
            @if($review->taker && $review->taker->photo)
              <img src="{{ asset('storage/' . $review->taker->photo) }}" alt="" class="w-10 h-10 rounded-full object-cover" />
            @else
              <div class="w-10 h-10 rounded-full bg-base-300 flex items-center justify-center flex-shrink-0">
                <x-heroicon-o-user class="w-5 h-5 text-base-content/20" />
              </div>
            @endif
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <div>
                  <span class="font-bold text-base-content">{{ $review->taker->name ?? 'Customer' }}</span>
                  <span class="text-xs text-base-content/40 ml-2">{{ $review->created_at->diffForHumans() }}</span>
                </div>
                <div class="flex gap-0.5">
                  @for($i = 1; $i <= 5; $i++)
                    <x-heroicon-s-star class="h-4 w-4 {{ $i <= $review->rating ? 'text-warning' : 'text-base-300' }}" />
                  @endfor
                </div>
              </div>

              @if($review->booking && $review->booking->service)
                <p class="text-xs text-base-content/40 mt-1">Service: {{ $review->booking->service->name }}</p>
              @endif

              @if($review->comment)
                <p class="mt-2 text-base-content/70">{{ $review->comment }}</p>
              @endif

              {{-- Existing replies --}}
              @if($review->replies->count())
                <div class="mt-4 ml-4 space-y-3">
                  @foreach($review->replies as $reply)
                    <div class="rounded-lg bg-base-200 p-3">
                      <span class="text-sm font-semibold text-base-content">{{ $reply->user->name ?? 'You' }}</span>
                      <span class="text-xs text-base-content/40 ml-2">{{ $reply->created_at->diffForHumans() }}</span>
                      <p class="text-sm text-base-content/70 mt-1">{{ $reply->comment }}</p>
                    </div>
                  @endforeach
                </div>
              @endif

              {{-- Reply form --}}
              @if($review->replies->where('user_id', auth()->id())->isEmpty())
                <div class="mt-4 ml-4">
                  <form method="POST" action="{{ route('review.reply', $review) }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="comment" placeholder="Write a reply..." class="input input-bordered input-sm flex-1" required />
                    <button type="submit" class="btn btn-primary btn-sm">Reply</button>
                  </form>
                </div>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="text-center py-16">
          <x-heroicon-o-chat-bubble-bottom-center-text class="w-12 h-12 text-base-content/20 mx-auto" />
          <p class="mt-4 text-lg text-base-content/50">No reviews yet.</p>
        </div>
      @endforelse
    </div>

    <div class="mt-8">
      {{ $reviews->links() }}
    </div>
  </div>
</section>

@endsection

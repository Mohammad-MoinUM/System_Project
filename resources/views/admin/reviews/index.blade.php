@extends('admin.layouts.app')

@section('title', 'Reviews Management')

@section('content')
<div class="card bg-base-100 shadow-lg">
    <div class="card-body">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <h2 class="card-title font-semibold text-xl">All Reviews ({{ $reviews->total() }})</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <form action="{{ route('admin.reviews.index') }}" method="GET" class="md:col-span-2">
                <input type="text" name="search" placeholder="Search reviews..." 
                    value="{{ $search }}" class="input input-bordered w-full" />
            </form>
            <select name="rating" class="select select-bordered" onchange="window.location='{{ route('admin.reviews.index') }}?rating=' + this.value">
                <option value="all" @if($rating_filter === 'all') selected @endif>All Ratings</option>
                <option value="5" @if($rating_filter === '5') selected @endif>5 Stars</option>
                <option value="4" @if($rating_filter === '4') selected @endif>4 Stars</option>
                <option value="3" @if($rating_filter === '3') selected @endif>3 Stars</option>
                <option value="2" @if($rating_filter === '2') selected @endif>2 Stars</option>
                <option value="1" @if($rating_filter === '1') selected @endif>1 Star</option>
            </select>
        </div>

        <div class="space-y-4">
            @forelse($reviews as $review)
                <div class="card bg-base-200">
                    <div class="card-body">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-4 mb-2">
                                    <span class="font-semibold text-lg">{{ $review->provider->name }}</span>
                                    <div class="flex items-center space-x-1">
                                        @for($i = 0; $i < 5; $i++)
                                            <svg class="w-4 h-4 @if($i < $review->rating) fill-warning @else fill-base-300 @endif" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                                <p class="text-base-content/60 text-sm mb-2">By {{ $review->taker->name }} • {{ $review->created_at->format('M d, Y') }}</p>
                                <p class="text-base-content">{{ $review->comment }}</p>
                            </div>
                            <button onclick="deleteReview({{ $review->id }})" class="btn btn-xs btn-error">Delete</button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-base-content/60">
                    No reviews found.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $reviews->links() }}
        </div>
    </div>
</div>

<script>
function deleteReview(reviewId) {
    if (confirm('Are you sure you want to delete this review?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.reviews.destroy", ":id") }}'.replace(':id', reviewId);
        form.innerHTML = '<input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection

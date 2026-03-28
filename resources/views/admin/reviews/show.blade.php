@extends('admin.layouts.app')

@section('title', 'Review Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="card-title text-2xl font-bold">Review for {{ $review->provider->name }}</h2>
                        <p class="text-base-content/60">{{ $review->created_at->format('M d, Y \a\t h:i A') }}</p>
                    </div>
                    <div class="flex items-center space-x-1">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-6 h-6 @if($i < $review->rating) fill-warning @else fill-base-300 @endif" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                </div>

                <div class="divider"></div>

                <div class="mb-6">
                    <h3 class="font-semibold text-lg mb-3">Review Comment</h3>
                    <div class="bg-base-200 p-4 rounded-lg">
                        <p class="text-base-content">{{ $review->comment }}</p>
                    </div>
                </div>

                @if($review->reply)
                    <div class="mb-6">
                        <h3 class="font-semibold text-lg mb-3">Provider's Reply</h3>
                        <div class="bg-info/10 p-4 rounded-lg border border-info/30">
                            <p class="text-base-content">{{ $review->reply }}</p>
                            <p class="text-sm text-base-content/60 mt-2">{{ $review->updated_at->format('M d, Y \a\t h:i A') }}</p>
                        </div>
                    </div>
                @endif

                <div class="divider"></div>

                <div class="card-actions">
                    <button onclick="deleteReview()" class="btn btn-error btn-sm">Delete Review</button>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title font-semibold mb-4">Reviewer</h3>
                <p class="font-semibold text-lg">{{ $review->taker->name }}</p>
                <p class="text-base-content/60">{{ $review->taker->email }}</p>
                <a href="{{ route('admin.users.show', $review->taker) }}" class="btn btn-ghost btn-sm mt-4">View Profile</a>
            </div>
        </div>

        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title font-semibold mb-4">Reviewed Provider</h3>
                <p class="font-semibold text-lg">{{ $review->provider->name }}</p>
                <p class="text-base-content/60">{{ $review->provider->email }}</p>
                <a href="{{ route('admin.users.show', $review->provider) }}" class="btn btn-ghost btn-sm mt-4">View Profile</a>
            </div>
        </div>
    </div>
</div>

<script>
function deleteReview() {
    if (confirm('Are you sure you want to delete this review?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.reviews.destroy", $review) }}';
        form.innerHTML = '<input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection

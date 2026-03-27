@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-base-content mb-2">Booking Details</h1>
                    <p class="text-base-content/70">Booking #{{ $booking->id }}</p>
                </div>
                <a href="{{ route('corporate.booking-history', $company->id) }}" class="btn btn-outline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Back to History
                </a>
            </div>
        </div>

        <!-- Status Card -->
        <div class="card bg-base-100 shadow-lg mb-6">
            <div class="card-body">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-base-content/70 mb-2">Booking Status</p>
                        <p class="text-2xl font-bold text-base-content">{{ $booking->service->name ?? 'N/A' }}</p>
                    </div>
                    <div class="badge badge-lg {{ 
                        $booking->status === 'completed' ? 'badge-success' :
                        ($booking->status === 'confirmed' ? 'badge-info' :
                        ($booking->status === 'cancelled' ? 'badge-error' : 'badge-warning'))
                    }}">
                        {{ ucfirst($booking->status) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid md:grid-cols-2 gap-6 mb-6">
            <!-- Left Column: Booking Information -->
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Booking Information</h2>
                    
                    <div class="mb-4">
                        <p class="text-sm text-base-content/70">Booking ID</p>
                        <p class="font-semibold text-base-content">#{{ $booking->id }}</p>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-base-content/70">Requested Date</p>
                        <p class="font-semibold text-base-content">{{ $booking->created_at->format('M d, Y - h:i A') }}</p>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-base-content/70">Service Type</p>
                        <p class="font-semibold text-base-content">{{ $booking->service->name ?? 'N/A' }}</p>
                    </div>

                    @if($booking->service)
                    <div class="mb-4">
                        <p class="text-sm text-base-content/70">Service Category</p>
                        <p class="font-semibold text-base-content">{{ $booking->service->category ?? 'N/A' }}</p>
                    </div>
                    @endif

                    <div class="mb-4">
                        <p class="text-sm text-base-content/70">Branch</p>
                        <p class="font-semibold text-base-content">{{ $booking->branch->branch_name ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-base-content/70">Company</p>
                        <p class="font-semibold text-base-content">{{ $company->company_name }}</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: People Information -->
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">People Involved</h2>

                    @if($booking->taker)
                    <div class="mb-6">
                        <p class="text-sm text-base-content/70 mb-2">Requested By</p>
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div class="bg-primary text-primary-content rounded-full w-10">
                                    <span class="font-bold">{{ substr($booking->taker->first_name, 0, 1) }}</span>
                                </div>
                            </div>
                            <div>
                                <p class="font-semibold text-base-content">{{ $booking->taker->first_name }} {{ $booking->taker->last_name }}</p>
                                <p class="text-sm text-base-content/70">{{ $booking->taker->email }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($booking->provider)
                    <div class="mb-6">
                        <p class="text-sm text-base-content/70 mb-2">Service Provider</p>
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div class="bg-secondary text-secondary-content rounded-full w-10">
                                    <span class="font-bold">{{ substr($booking->provider->first_name, 0, 1) }}</span>
                                </div>
                            </div>
                            <div>
                                <p class="font-semibold text-base-content">{{ $booking->provider->first_name }} {{ $booking->provider->last_name }}</p>
                                <p class="text-sm text-base-content/70">{{ $booking->provider->email }}</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-warning mb-6">
                        <svg class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v2m0-6v-2m0 0V7a2 2 0 012-2h2.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2h-2.586a1 1 0 01-.707-.293l-5.414-5.414a1 1 0 01-.293-.707z"></path></svg>
                        <span>Service Provider Not Yet Assigned</span>
                    </div>
                    @endif

                    @if($booking->approved_by)
                    <div>
                        <p class="text-sm text-base-content/70 mb-2">Approved By</p>
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div class="bg-success text-success-content rounded-full w-10">
                                    <span class="font-bold">{{ substr($booking->approved_by->first_name ?? 'A', 0, 1) }}</span>
                                </div>
                            </div>
                            <div>
                                <p class="font-semibold text-base-content">{{ $booking->approved_by->first_name ?? 'N/A' }} {{ $booking->approved_by->last_name ?? 'N/A' }}</p>
                                <p class="text-sm text-base-content/70">{{ $booking->approved_at ? $booking->approved_at->format('M d, Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Details Section -->
        <div class="card bg-base-100 shadow-lg mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Additional Details</h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    @if($booking->notes)
                    <div>
                        <p class="text-sm text-base-content/70 mb-2">Notes</p>
                        <p class="text-base-content">{{ $booking->notes }}</p>
                    </div>
                    @endif

                    @if($booking->total)
                    <div>
                        <p class="text-sm text-base-content/70 mb-2">Total Cost</p>
                        <p class="text-2xl font-bold text-base-content">${{ number_format($booking->total, 2) }}</p>
                    </div>
                    @endif

                    @if($booking->created_at)
                    <div>
                        <p class="text-sm text-base-content/70 mb-2">Created At</p>
                        <p class="text-base-content">{{ $booking->created_at->format('M d, Y \a\t h:i A') }}</p>
                    </div>
                    @endif

                    @if($booking->updated_at)
                    <div>
                        <p class="text-sm text-base-content/70 mb-2">Last Updated</p>
                        <p class="text-base-content">{{ $booking->updated_at->format('M d, Y \a\t h:i A') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        @if($booking->reviews->count() > 0)
        <div class="card bg-base-100 shadow-lg mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Reviews ({{ $booking->reviews->count() }})</h2>
                
                @foreach($booking->reviews as $review)
                <div class="mb-4 pb-4 border-b last:border-b-0">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-semibold text-base-content">{{ $review->reviewer->first_name ?? 'N/A' }} {{ $review->reviewer->last_name ?? 'N/A' }}</p>
                            <p class="text-sm text-base-content/70">{{ $review->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="badge badge-warning">{{ $review->rating ?? 0 }}/5</div>
                    </div>
                    <p class="text-base-content">{{ $review->comment ?? 'No comment' }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex gap-3 justify-center">
            <a href="{{ route('corporate.booking-history', $company->id) }}" class="btn btn-outline">
                Back to History
            </a>
            <a href="{{ route('corporate.dashboard') }}" class="btn btn-primary">
                Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection

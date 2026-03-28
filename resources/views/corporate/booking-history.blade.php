@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-base-content mb-2">Booking History</h1>
                    <p class="text-base-content/70">View all corporate bookings and their status</p>
                </div>
                <a href="{{ route('corporate.dashboard') }}" class="btn btn-outline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Company Info -->
        <div class="card bg-base-100 shadow-lg mb-6">
            <div class="card-body">
                <p class="text-sm text-base-content/70">Company: <span class="font-semibold text-base-content">{{ $company->company_name }}</span></p>
            </div>
        </div>

        <!-- Filters -->
        <div class="card bg-base-100 shadow-lg mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">Filters</h3>
                <form method="GET" class="flex flex-wrap gap-4" action="{{ route('corporate.booking-history', $company->id) }}">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Status</span>
                        </label>
                        <select name="status" class="select select-bordered w-full max-w-xs">
                            <option value="">All Statuses</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                            <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
                            <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Branch</span>
                        </label>
                        <select name="branch_id" class="select select-bordered w-full max-w-xs">
                            <option value="">All Branches</option>
                            @foreach($company->branches()->where('is_active', true)->get() as $branch)
                            <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control flex flex-row items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Filter
                        </button>
                        <a href="{{ route('corporate.booking-history', $company->id) }}" class="btn btn-ghost">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bookings Table -->
        @if($bookings->count() > 0)
        <div class="card bg-base-100 shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-base-200">
                        <tr>
                            <th>Booking ID</th>
                            <th>Service</th>
                            <th>Provider</th>
                            <th>Requested By</th>
                            <th>Branch</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                        <tr class="hover">
                            <td class="font-semibold">#{{ $booking->id }}</td>
                            <td>{{ $booking->service->name ?? 'N/A' }}</td>
                            <td>
                                @if($booking->provider)
                                    <div class="flex items-center gap-2">
                                        <div class="avatar placeholder">
                                            <div class="bg-primary text-primary-content rounded-full w-8">
                                                <span class="text-xs">{{ substr($booking->provider->first_name, 0, 1) }}</span>
                                            </div>
                                        </div>
                                        {{ $booking->provider->first_name }} {{ $booking->provider->last_name }}
                                    </div>
                                @else
                                    <span class="text-base-content/50">Not Assigned</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->taker)
                                    {{ $booking->taker->first_name }} {{ $booking->taker->last_name }}
                                @else
                                    <span class="text-base-content/50">N/A</span>
                                @endif
                            </td>
                            <td>{{ $booking->branch->branch_name ?? 'N/A' }}</td>
                            <td>{{ $booking->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="badge {{ 
                                    $booking->status === 'completed' ? 'badge-success' :
                                    ($booking->status === 'confirmed' ? 'badge-info' :
                                    ($booking->status === 'cancelled' ? 'badge-error' : 'badge-warning'))
                                }}">
                                    {{ ucfirst($booking->status) }}
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('corporate.booking-details', [$company->id, $booking->id]) }}" class="btn btn-sm btn-ghost">
                                    View Details
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center mt-8">
            {{ $bookings->links() }}
        </div>
        @else
        <div class="alert alert-info">
            <svg class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>No bookings found. Try adjusting your filters or create a new booking.</span>
        </div>
        @endif
    </div>
</div>
@endsection

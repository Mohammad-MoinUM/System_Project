@extends('admin.layouts.app')

@section('title', 'Booking #' . $booking->id)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="card-title text-2xl font-bold">Booking #{{ $booking->id }}</h2>
                        <p class="text-base-content/60">{{ $booking->created_at->format('M d, Y \a\t h:i A') }}</p>
                    </div>
                    <span class="badge badge-lg @if($booking->status === 'completed') badge-success @elseif($booking->status === 'cancelled') badge-error @elseif($booking->status === 'pending') badge-warning @else badge-info @endif">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>

                <div class="divider"></div>

                <div class="space-y-6">
                    <div>
                        <h3 class="font-semibold text-lg mb-3">Service Details</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-base-content/60">Service Name</p>
                                <p class="font-semibold">{{ $booking->service->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">Category</p>
                                <p class="font-semibold">{{ $booking->service->category ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">Price</p>
                                <p class="font-semibold text-lg text-primary">{{ $booking->service->price ?? 'N/A' }} BDT</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg mb-3">Booking Info</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-base-content/60">Scheduled Date</p>
                                <p class="font-semibold">{{ $booking->scheduled_date ?? 'Not scheduled' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">Payment Status</p>
                                <p class="font-semibold">{{ $booking->payment_status ?? 'Pending' }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-sm text-base-content/60">Special Notes</p>
                                <p class="font-semibold">{{ $booking->notes ?? 'No special notes' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($booking->status !== 'completed' && $booking->status !== 'cancelled')
                    <div class="divider"></div>
                    <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-error btn-sm" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel Booking</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title font-semibold mb-4">Customer</h3>
                <p class="font-semibold text-lg">{{ $booking->taker->name }}</p>
                <p class="text-base-content/60">{{ $booking->taker->email }}</p>
                <p class="text-base-content/60">{{ $booking->taker->phone ?? 'No phone' }}</p>
                <a href="{{ route('admin.users.show', $booking->taker) }}" class="btn btn-ghost btn-sm mt-4">View Profile</a>
            </div>
        </div>

        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title font-semibold mb-4">Provider</h3>
                <p class="font-semibold text-lg">{{ $booking->provider->name }}</p>
                <p class="text-base-content/60">{{ $booking->provider->email }}</p>
                <p class="text-base-content/60">{{ $booking->provider->phone ?? 'No phone' }}</p>
                <a href="{{ route('admin.users.show', $booking->provider) }}" class="btn btn-ghost btn-sm mt-4">View Profile</a>
            </div>
        </div>
    </div>
</div>
@endsection

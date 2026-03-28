@extends('admin.layouts.app')

@section('title', 'Bookings Management')

@section('content')
<div class="card bg-base-100 shadow-lg">
    <div class="card-body">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <h2 class="card-title font-semibold text-xl">All Bookings ({{ $bookings->total() }})</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <form action="{{ route('admin.bookings.index') }}" method="GET" class="md:col-span-2">
                <input type="text" name="search" placeholder="Search by ID, customer, or provider..." 
                    value="{{ $search }}" class="input input-bordered w-full" />
            </form>
            <select name="status" class="select select-bordered" onchange="window.location='{{ route('admin.bookings.index') }}?status=' + this.value">
                <option value="all" @if($status_filter === 'all') selected @endif>All Status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @if($status_filter === $status) selected @endif>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr class="text-base-content">
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Provider</th>
                        <th>Service</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td class="font-semibold">#{{ $booking->id }}</td>
                            <td>{{ $booking->taker->name }}</td>
                            <td>{{ $booking->provider->name }}</td>
                            <td>{{ $booking->service->name ?? 'N/A' }}</td>
                            <td class="font-semibold">{{ $booking->service->price ?? 'N/A' }} BDT</td>
                            <td>
                                <span class="badge @if($booking->status === 'completed') badge-success @elseif($booking->status === 'cancelled') badge-error @elseif($booking->status === 'pending') badge-warning @else badge-info @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td>{{ $booking->created_at->format('M d, Y') }}</td>
                            <td class="space-x-2">
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-xs btn-ghost">View</a>
                                @if($booking->status !== 'completed' && $booking->status !== 'cancelled')
                                    <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-error" onclick="return confirm('Cancel this booking?')">Cancel</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-base-content/60">No bookings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection

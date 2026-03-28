@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-12">
    <div class="card bg-base-100 shadow-lg">
        <div class="card-body">
            <h3 class="card-title text-lg font-semibold text-primary">Total Users</h3>
            <p class="text-4xl font-bold mt-4">{{ $stats['total_users'] }}</p>
        </div>
    </div>

    <div class="card bg-base-100 shadow-lg">
        <div class="card-body">
            <h3 class="card-title text-lg font-semibold text-accent">Customers</h3>
            <p class="text-4xl font-bold mt-4">{{ $stats['total_customers'] }}</p>
        </div>
    </div>

    <div class="card bg-base-100 shadow-lg">
        <div class="card-body">
            <h3 class="card-title text-lg font-semibold text-info">Providers</h3>
            <p class="text-4xl font-bold mt-4">{{ $stats['total_providers'] }}</p>
        </div>
    </div>

    <div class="card bg-base-100 shadow-lg">
        <div class="card-body">
            <h3 class="card-title text-lg font-semibold text-warning">Pending Bookings</h3>
            <p class="text-4xl font-bold mt-4">{{ $stats['pending_bookings'] }}</p>
        </div>
    </div>

    <div class="card bg-base-100 shadow-lg">
        <div class="card-body">
            <h3 class="card-title text-lg font-semibold text-success">Completed Bookings</h3>
            <p class="text-4xl font-bold mt-4">{{ $stats['completed_bookings'] }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
    <div class="card bg-base-100 shadow-lg">
        <div class="card-body">
            <h3 class="card-title font-semibold">Total Services</h3>
            <p class="text-3xl font-bold text-primary mt-2">{{ $stats['total_services'] }}</p>
            <p class="text-sm text-base-content/60 mt-2">{{ $stats['active_services'] }} active</p>
        </div>
    </div>

    <div class="card bg-base-100 shadow-lg">
        <div class="card-body">
            <h3 class="card-title font-semibold">Total Bookings</h3>
            <p class="text-3xl font-bold text-info mt-2">{{ $stats['total_bookings'] }}</p>
        </div>
    </div>

    <div class="card bg-base-100 shadow-lg">
        <div class="card-body">
            <h3 class="card-title font-semibold">Total Reviews</h3>
            <p class="text-3xl font-bold text-warning mt-2">{{ $stats['total_reviews'] }}</p>
            <p class="text-sm text-base-content/60 mt-2">Avg: {{ number_format($stats['avg_rating'], 1) }} ⭐</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Bookings -->
    <div class="card bg-base-100 shadow-lg">
        <div class="card-body">
            <h3 class="card-title font-semibold mb-4">Recent Bookings</h3>
            <div class="overflow-x-auto">
                <table class="table table-compact w-full">
                    <thead>
                        <tr class="border-b border-base-300">
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_bookings as $booking)
                            <tr class="border-b border-base-200 hover:bg-base-200 cursor-pointer" onclick="window.location='{{ route('admin.bookings.show', $booking) }}'">
                                <td class="font-semibold">#{{ $booking->id }}</td>
                                <td>{{ $booking->taker->name }}</td>
                                <td>
                                    <span class="badge @if($booking->status === 'completed') badge-success @elseif($booking->status === 'pending') badge-warning @else badge-info @endif">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-base-content/60">No bookings yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-actions mt-4">
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-ghost">View All</a>
            </div>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="card bg-base-100 shadow-lg">
        <div class="card-body">
            <h3 class="card-title font-semibold mb-4">Recent Users</h3>
            <div class="space-y-3">
                @forelse($recent_users as $user)
                    <a href="{{ route('admin.users.show', $user) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-base-200">
                        <div>
                            <p class="font-semibold">{{ $user->name }}</p>
                            <p class="text-sm text-base-content/60">{{ $user->email }}</p>
                        </div>
                        <span class="badge badge-outline">{{ ucfirst($user->role) }}</span>
                    </a>
                @empty
                    <p class="text-base-content/60 text-center py-4">No users yet.</p>
                @endforelse
            </div>
            <div class="card-actions mt-4">
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-ghost">View All</a>
            </div>
        </div>
    </div>
</div>
@endsection

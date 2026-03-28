@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-base-content mb-2">Corporate Dashboard</h1>
            <p class="text-base-content/70">Manage your company, branches, and service requests</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Company Overview Card -->
        <div class="card bg-base-100 shadow-lg mb-8">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="card-title text-2xl">{{ $company->company_name }}</h2>
                        <p class="text-base-content/70">{{ $company->address }}, {{ $company->city }}</p>
                        <p class="text-sm text-base-content/50 mt-2">Reg: {{ $company->company_registration_number }}</p>
                    </div>
                    <div class="badge badge-lg {{ $company->status === 'approved' ? 'badge-success' : 'badge-warning' }}">
                        {{ ucfirst($company->status) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="stat bg-base-100 shadow">
                <div class="stat-title">Branches</div>
                <div class="stat-value text-2xl">{{ $stats['total_branches'] }}</div>
            </div>

            <div class="stat bg-base-100 shadow">
                <div class="stat-title">Staff Members</div>
                <div class="stat-value text-2xl">{{ $stats['total_staff'] }}</div>
            </div>

            <div class="stat bg-base-100 shadow">
                <div class="stat-title">Pending Requests</div>
                <div class="stat-value text-2xl text-warning">{{ $stats['pending_requests'] }}</div>
            </div>

            <div class="stat bg-base-100 shadow">
                <div class="stat-title">This Month</div>
                <div class="stat-value text-2xl">{{ isset($monthlySpend) ? $monthlySpend : '0' }}</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-3 mb-8">
            @if(in_array($userRole, ['admin', 'manager']))
            <a href="{{ route('corporate.branches.index', $company->id) }}" class="btn btn-outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 19V5m-2 0H7m12 0a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2m12 0V5a2 2 0 00-2-2H7a2 2 0 00-2 2v2m12 0h-2.5M7 11h5"></path></svg>
                Manage Branches
            </a>
            @endif

            @if($userRole === 'admin')
            <a href="{{ route('corporate.staff.index', $company->id) }}" class="btn btn-outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Manage Staff
            </a>
            @endif

            @if(in_array($userRole, ['admin', 'manager', 'requester', 'approver']))
            <a href="{{ route('corporate.requests.index', $company->id) }}" class="btn btn-outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                View Requests
            </a>
            @endif

            @if(in_array($userRole, ['admin', 'finance']))
            <a href="{{ route('corporate.invoices.index', $company->id) }}" class="btn btn-outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                View Invoices
            </a>
            @endif

            <a href="{{ route('corporate.booking-history', $company->id) }}" class="btn btn-outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Booking History
            </a>
        </div>

        <!-- Pending Approvals -->
        @if($pendingApprovals->count() > 0)
        <div class="card bg-base-100 shadow-lg mb-8">
            <div class="card-body">
                <h2 class="card-title">Pending Approvals ({{ $pendingApprovals->count() }})</h2>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Branch</th>
                                <th>Requested By</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingApprovals as $request)
                            <tr>
                                <td>{{ $request->service->name ?? 'N/A' }}</td>
                                <td>{{ $request->branch->branch_name ?? 'N/A' }}</td>
                                <td>{{ $request->requester->name ?? 'N/A' }}</td>
                                <td>{{ $request->requested_date->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('corporate.requests.show', [$company->id, $request->id]) }}" class="btn btn-sm btn-primary">
                                        Review
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Bookings -->
        @if($recentBookings->count() > 0)
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title">Recent Bookings</h2>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Provider</th>
                                <th>Branch</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBookings as $booking)
                            <tr>
                                <td>{{ $booking->service->name ?? 'N/A' }}</td>
                                <td>{{ $booking->provider->name ?? 'N/A' }}</td>
                                <td>{{ $booking->branch->branch_name ?? 'N/A' }}</td>
                                <td>
                                    <div class="badge {{ $booking->status === 'completed' ? 'badge-success' : 'badge-info' }}">
                                        {{ ucfirst($booking->status) }}
                                    </div>
                                </td>
                                <td>{{ $booking->booking_date->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

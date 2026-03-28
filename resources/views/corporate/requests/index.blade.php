@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-base-content mb-2">Service Requests</h1>
                <p class="text-base-content/70">Manage and approve service requests</p>
            </div>
            <a href="{{ route('corporate.requests.create', $company->id) }}" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Request
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-6">{{ session('success') }}</div>
        @endif

        <!-- Filters -->
        <div class="mb-6 flex gap-2 flex-wrap">
            <a href="{{ route('corporate.requests.index', $company->id) }}?status=pending" class="btn btn-sm {{ request('status') === 'pending' ? 'btn-primary' : 'btn-outline' }}">Pending</a>
            <a href="{{ route('corporate.requests.index', $company->id) }}?status=approved" class="btn btn-sm {{ request('status') === 'approved' ? 'btn-primary' : 'btn-outline' }}">Approved</a>
            <a href="{{ route('corporate.requests.index', $company->id) }}?status=rejected" class="btn btn-sm {{ request('status') === 'rejected' ? 'btn-primary' : 'btn-outline' }}">Rejected</a>
            <a href="{{ route('corporate.requests.index', $company->id) }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline' }}">All</a>
        </div>

        @if($requests->count() > 0)
        <div class="card bg-base-100 shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Branch</th>
                            <th>Requested By</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $request)
                        <tr>
                            <td>{{ $request->service->name ?? 'N/A' }}</td>
                            <td>{{ $request->branch->branch_name ?? 'N/A' }}</td>
                            <td>{{ $request->requester->name ?? 'N/A' }}</td>
                            <td>{{ $request->requested_date->format('M d, Y') }}</td>
                            <td>
                                <div class="badge {{ 
                                    $request->status === 'approved' ? 'badge-success' : 
                                    ($request->status === 'rejected' ? 'badge-error' : 'badge-warning')
                                }}">
                                    {{ ucfirst($request->status) }}
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('corporate.requests.show', [$company->id, $request->id]) }}" class="btn btn-sm btn-outline">
                                    View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body text-center">
                <p class="text-base-content/70 mb-4">No service requests yet.</p>
                <a href="{{ route('corporate.requests.create', $company->id) }}" class="btn btn-primary mx-auto">
                    Create First Request
                </a>
            </div>
        </div>
        @endif

        <div class="mt-8">
            <a href="{{ route('corporate.dashboard') }}" class="btn btn-outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-start mb-8">
            <div>
                <h1 class="text-3xl font-bold text-base-content mb-2">Service Request Details</h1>
                <p class="text-base-content/70">Request #{{ $request->id }}</p>
            </div>
            <div class="badge badge-lg {{ 
                $request->status === 'approved' ? 'badge-success' : 
                ($request->status === 'rejected' ? 'badge-error' : 'badge-warning')
            }}">
                {{ ucfirst($request->status) }}
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-6">{{ session('success') }}</div>
        @endif

        <!-- Request Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-semibold text-base-content mb-4">Request Information</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-base-content/70">Service</p>
                            <p class="font-semibold">{{ $request->service->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/70">Branch</p>
                            <p class="font-semibold">{{ $request->branch->branch_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/70">Required Date</p>
                            <p class="font-semibold">{{ $request->requested_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/70">Budget</p>
                            <p class="font-semibold">{{ $request->budget ? '$' . number_format($request->budget, 2) : 'Not specified' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-semibold text-base-content mb-4">Requester Information</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-base-content/70">Requested By</p>
                            <p class="font-semibold">{{ $request->requester->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/70">Email</p>
                            <p class="font-semibold">{{ $request->requester->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/70">Requested On</p>
                            <p class="font-semibold">{{ $request->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        @if($request->status !== 'pending')
                        <div>
                            <p class="text-sm text-base-content/70">Approved By</p>
                            <p class="font-semibold">{{ $request->approver->name ?? 'N/A' }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        @if($request->description)
        <div class="card bg-base-100 shadow mb-8">
            <div class="card-body">
                <h3 class="font-semibold text-base-content mb-4">Description</h3>
                <p class="text-base-content/70 whitespace-pre-wrap">{{ $request->description }}</p>
            </div>
        </div>
        @endif

        <!-- Approval Actions -->
        @if($request->status === 'pending' && auth()->user()->canApproveInCompany($company->id))
        <div class="card bg-base-100 shadow mb-8">
            <div class="card-body">
                <h3 class="font-semibold text-base-content mb-4">Approve or Reject Request</h3>
                <div class="flex gap-3">
                    <form action="{{ route('corporate.requests.approve', [$company->id, $request->id]) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="btn btn-success w-full">Approve Request</button>
                    </form>
                    <form action="{{ route('corporate.requests.reject', [$company->id, $request->id]) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="btn btn-error w-full">Reject Request</button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <div>
            <a href="{{ route('corporate.requests.index', $company->id) }}" class="btn btn-outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Requests
            </a>
        </div>
    </div>
</div>
@endsection

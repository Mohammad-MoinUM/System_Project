@extends('admin.layouts.app')

@section('title', 'Pending Provider Verification')

@section('content')
<div class="card bg-base-100 shadow-lg">
    <div class="card-body">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <h2 class="card-title font-semibold text-xl">Pending Provider Approvals ({{ $providers->total() }})</h2>
        </div>

        @if(session('success'))
            <div class="alert alert-success shadow-lg mb-6">
                <svg class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($providers->count() === 0)
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-base-content/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-base-content/60 text-lg">No pending provider verifications</p>
                <p class="text-base-content/40 text-sm mt-2">All providers have been reviewed!</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($providers as $provider)
                    <div class="card bg-base-200 hover:shadow-md transition-shadow">
                        <div class="card-body p-6">
                            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-lg">{{ $provider->name }}</h3>
                                    <p class="text-base-content/60 text-sm">{{ $provider->email }}</p>
                                    
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
                                        <div>
                                            <p class="text-xs text-base-content/60">Expertise</p>
                                            <p class="font-semibold text-sm">{{ $provider->expertise ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-base-content/60">Experience</p>
                                            <p class="font-semibold text-sm">{{ $provider->experience_years ?? 'N/A' }} years</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-base-content/60">Location</p>
                                            <p class="font-semibold text-sm">{{ $provider->city ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-base-content/60">Submitted</p>
                                            <p class="font-semibold text-sm">{{ $provider->created_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-3 lg:min-w-max">
                                    <a href="{{ route('admin.providers.show', $provider) }}" class="btn btn-sm btn-primary">
                                        Review Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $providers->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title font-semibold">Pending Review</h3>
            <p class="text-4xl font-bold text-warning">{{ $providers->total() }}</p>
        </div>
    </div>

    <a href="{{ route('admin.providers.approved') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
        <div class="card-body cursor-pointer">
            <h3 class="card-title font-semibold">Approved Providers</h3>
            <p class="text-4xl font-bold text-success">
                {{ \App\Models\User::where('role', 'provider')->where('verification_status', 'approved')->count() }}
            </p>
        </div>
    </a>

    <a href="{{ route('admin.providers.rejected') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
        <div class="card-body cursor-pointer">
            <h3 class="card-title font-semibold">Rejected Providers</h3>
            <p class="text-4xl font-bold text-error">
                {{ \App\Models\User::where('role', 'provider')->where('verification_status', 'rejected')->count() }}
            </p>
        </div>
    </a>
</div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Rejected Providers')

@section('content')
<div class="card bg-base-100 shadow-lg">
    <div class="card-body">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <h2 class="card-title font-semibold text-xl">Rejected Providers ({{ $providers->total() }})</h2>
            <a href="{{ route('admin.providers.pending') }}" class="btn btn-sm btn-ghost">← Back to Pending</a>
        </div>

        @if($providers->count() === 0)
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-base-content/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-base-content/60 text-lg">No rejected providers</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($providers as $provider)
                    <div class="card bg-base-200">
                        <div class="card-body">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-lg">{{ $provider->name }}</h3>
                                    <p class="text-base-content/60">{{ $provider->email }}</p>
                                    
                                    @if($provider->rejection_reason)
                                        <div class="mt-3 bg-error/10 p-3 rounded-lg border-l-4 border-error">
                                            <p class="text-sm font-semibold text-error mb-1">Rejection Reason:</p>
                                            <p class="text-sm text-base-content/80">{{ $provider->rejection_reason }}</p>
                                        </div>
                                    @endif

                                    <p class="text-sm text-base-content/60 mt-3">Rejected: {{ $provider->verified_at->format('M d, Y') }}</p>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <a href="{{ route('admin.providers.show', $provider) }}" class="btn btn-sm btn-ghost">Review</a>
                                    <a href="{{ route('admin.users.show', $provider) }}" class="btn btn-sm btn-info">Profile</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $providers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

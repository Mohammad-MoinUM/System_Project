@extends('admin.layouts.app')

@section('title', 'Approved Providers')

@section('content')
<div class="card bg-base-100 shadow-lg">
    <div class="card-body">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <h2 class="card-title font-semibold text-xl">Approved Providers ({{ $providers->total() }})</h2>
            <a href="{{ route('admin.providers.pending') }}" class="btn btn-sm btn-ghost">← Back to Pending</a>
        </div>

        @if($providers->count() === 0)
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-base-content/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-base-content/60 text-lg">No approved providers yet</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr class="text-base-content">
                            <th>Name</th>
                            <th>Email</th>
                            <th>Expertise</th>
                            <th>Experience</th>
                            <th>Approved Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($providers as $provider)
                            <tr>
                                <td class="font-semibold">{{ $provider->name }}</td>
                                <td>{{ $provider->email }}</td>
                                <td>{{ $provider->expertise ?? 'N/A' }}</td>
                                <td>{{ $provider->experience_years ?? 'N/A' }} years</td>
                                <td>{{ $provider->verified_at->format('M d, Y') }}</td>
                                <td class="space-x-2">
                                    <a href="{{ route('admin.providers.show', $provider) }}" class="btn btn-xs btn-ghost">View</a>
                                    <a href="{{ route('admin.users.show', $provider) }}" class="btn btn-xs btn-info">Profile</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $providers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

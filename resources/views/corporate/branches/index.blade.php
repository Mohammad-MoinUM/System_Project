@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-base-content mb-2">Branches</h1>
                <p class="text-base-content/70">Manage your company branches</p>
            </div>
            <a href="{{ route('corporate.branches.create', $company->id) }}" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Branch
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-6">{{ session('success') }}</div>
        @endif

        @if($branches->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($branches as $branch)
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h2 class="card-title text-lg">{{ $branch->branch_name }}</h2>
                    <p class="text-sm text-base-content/70">{{ $branch->address }}</p>
                    <p class="text-sm text-base-content/70">{{ $branch->city }}</p>
                    
                    @if($branch->manager)
                        <p class="text-sm mt-3">
                            <span class="label-text font-semibold">Manager:</span> {{ $branch->manager->name }}
                        </p>
                    @endif

                    <div class="badge {{ $branch->is_active ? 'badge-success' : 'badge-ghost' }} mt-4">
                        {{ $branch->is_active ? 'Active' : 'Inactive' }}
                    </div>

                    <div class="card-actions justify-end mt-6 gap-2">
                        <a href="{{ route('corporate.branches.edit', [$company->id, $branch->id]) }}" class="btn btn-sm btn-outline">
                            Edit
                        </a>
                        <form action="{{ route('corporate.branches.destroy', [$company->id, $branch->id]) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline btn-error" onclick="return confirm('Are you sure?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body text-center">
                <p class="text-base-content/70 mb-4">No branches yet. Create one to get started.</p>
                <a href="{{ route('corporate.branches.create', $company->id) }}" class="btn btn-primary mx-auto">
                    Create First Branch
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

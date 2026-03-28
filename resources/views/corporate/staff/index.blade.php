@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-base-content mb-2">Staff Management</h1>
                <p class="text-base-content/70">Manage company staff and permissions</p>
            </div>
            <a href="{{ route('corporate.staff.create', $company->id) }}" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Invite Staff
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-6">{{ session('success') }}</div>
        @endif

        @if($staff->count() > 0)
        <div class="card bg-base-100 shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staff as $member)
                        <tr>
                            <td>{{ $member->user->name }}</td>
                            <td>{{ $member->user->email }}</td>
                            <td>
                                <div class="badge badge-primary">{{ ucfirst($member->role) }}</div>
                            </td>
                            <td>
                                <div class="badge {{ $member->is_active ? 'badge-success' : 'badge-ghost' }}">
                                    {{ $member->is_active ? 'Active' : 'Inactive' }}
                                </div>
                            </td>
                            <td>{{ $member->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <button tabindex="0" class="btn btn-sm btn-ghost">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path></svg>
                                    </button>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                        <li><a href="{{ route('corporate.staff.edit', [$company->id, $member->id]) }}">Edit Role</a></li>
                                        <li>
                                            <form method="POST" action="{{ route('corporate.staff.destroy', [$company->id, $member->id]) }}" style="display:inline;" onsubmit="return confirm('Remove staff member?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-error">Remove</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
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
                <p class="text-base-content/70 mb-4">No staff members yet. Invite one to get started.</p>
                <a href="{{ route('corporate.staff.create', $company->id) }}" class="btn btn-primary mx-auto">
                    Invite First Staff
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

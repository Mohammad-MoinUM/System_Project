@extends('admin.layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="card bg-base-100 shadow-lg">
    <div class="card-body">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <h2 class="card-title font-semibold text-xl">All Users ({{ $users->total() }})</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <form action="{{ route('admin.users.index') }}" method="GET" class="md:col-span-2">
                <input type="text" name="search" placeholder="Search by name or email..." 
                    value="{{ $search }}" class="input input-bordered w-full" />
            </form>
            <select name="role" class="select select-bordered" onchange="window.location='{{ route('admin.users.index') }}?role=' + this.value">
                <option value="all" @if($role_filter === 'all') selected @endif>All Roles</option>
                <option value="admin" @if($role_filter === 'admin') selected @endif>Admin</option>
                <option value="provider" @if($role_filter === 'provider') selected @endif>Provider</option>
                <option value="customer" @if($role_filter === 'customer') selected @endif>Customer</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr class="text-base-content">
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="font-semibold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge @if($user->role === 'admin') badge-error @elseif($user->role === 'provider') badge-primary @else badge-accent @endif">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($user->role !== 'admin' && !$user->onboarding_completed)
                                    <span class="badge badge-warning">Incomplete</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            </td>
                            <td class="space-x-2">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-xs btn-ghost">View</a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-xs btn-warning">Edit</a>
                                <button onclick="deleteUser({{ $user->id }})" class="btn btn-xs btn-error">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-base-content/60">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
</div>

<script>
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.users.destroy", ":id") }}'.replace(':id', userId);
        form.innerHTML = '<input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-base-content mb-8">Edit Staff Role</h1>

        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <form action="{{ route('corporate.staff.update', [$company->id, $membership->id]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
                        <div class="alert alert-error mb-6">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-control">
                        <label for="name" class="label">
                            <span class="label-text font-semibold">Staff Name</span>
                        </label>
                        <input id="name" type="text" disabled class="input input-bordered w-full" value="{{ $membership->user->name }}">
                    </div>

                    <div class="form-control mt-4">
                        <label for="email" class="label">
                            <span class="label-text font-semibold">Email</span>
                        </label>
                        <input id="email" type="email" disabled class="input input-bordered w-full" value="{{ $membership->user->email }}">
                    </div>

                    <div class="form-control mt-4">
                        <label for="role" class="label">
                            <span class="label-text font-semibold">Role *</span>
                        </label>
                        <select id="role" name="role" required class="select select-bordered w-full">
                            <option value="admin" {{ old('role', $membership->role) === 'admin' ? 'selected' : '' }}>Admin - Full access</option>
                            <option value="manager" {{ old('role', $membership->role) === 'manager' ? 'selected' : '' }}>Manager - Manage branches and staff</option>
                            <option value="requester" {{ old('role', $membership->role) === 'requester' ? 'selected' : '' }}>Requester - Request services</option>
                            <option value="approver" {{ old('role', $membership->role) === 'approver' ? 'selected' : '' }}>Approver - Approve service requests</option>
                            <option value="finance" {{ old('role', $membership->role) === 'finance' ? 'selected' : '' }}>Finance - Manage billing</option>
                        </select>
                    </div>

                    <div class="form-control mt-4">
                        <label for="is_active" class="label cursor-pointer">
                            <span class="label-text">Active Member</span>
                            <input id="is_active" name="is_active" type="checkbox" class="checkbox" value="1" {{ old('is_active', $membership->is_active) ? 'checked' : '' }}>
                        </label>
                    </div>

                    <div class="divider my-6">Role Permissions</div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 text-sm bg-base-200 p-4 rounded">
                        <div>
                            <strong>Admin:</strong>
                            <p class="text-base-content/70">Full access to all company features</p>
                        </div>
                        <div>
                            <strong>Manager:</strong>
                            <p class="text-base-content/70">Manage branches, staff, and view requests</p>
                        </div>
                        <div>
                            <strong>Requester:</strong>
                            <p class="text-base-content/70">Request and track services</p>
                        </div>
                        <div>
                            <strong>Approver:</strong>
                            <p class="text-base-content/70">Approve service requests and view bookings</p>
                        </div>
                        <div>
                            <strong>Finance:</strong>
                            <p class="text-base-content/70">View invoices and billing information</p>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-8">
                        <button type="submit" class="btn btn-primary flex-1">Update Role</button>
                        <a href="{{ route('corporate.staff.index', $company->id) }}" class="btn btn-outline flex-1">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

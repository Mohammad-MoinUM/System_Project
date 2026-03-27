@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-base-content mb-8">Invite Staff Member</h1>

        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <form action="{{ route('corporate.staff.store', $company->id) }}" method="POST">
                    @csrf

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
                        <label for="email" class="label">
                            <span class="label-text font-semibold">Staff Email *</span>
                        </label>
                        <input id="email" name="email" type="email" required class="input input-bordered w-full" placeholder="staff@example.com" value="{{ old('email') }}">
                        <label class="label">
                            <span class="label-text-alt">An account will be created if doesn't exist</span>
                        </label>
                    </div>

                    <div class="form-control mt-4">
                        <label for="first_name" class="label">
                            <span class="label-text font-semibold">First Name *</span>
                        </label>
                        <input id="first_name" name="first_name" type="text" required class="input input-bordered w-full" placeholder="First name" value="{{ old('first_name') }}">
                    </div>

                    <div class="form-control mt-4">
                        <label for="last_name" class="label">
                            <span class="label-text font-semibold">Last Name *</span>
                        </label>
                        <input id="last_name" name="last_name" type="text" required class="input input-bordered w-full" placeholder="Last name" value="{{ old('last_name') }}">
                    </div>

                    <div class="form-control mt-4">
                        <label for="role" class="label">
                            <span class="label-text font-semibold">Role *</span>
                        </label>
                        <select id="role" name="role" required class="select select-bordered w-full">
                            <option value="">Select a role</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin - Full access</option>
                            <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager - Manage branches and staff</option>
                            <option value="requester" {{ old('role') === 'requester' ? 'selected' : '' }}>Requester - Request services</option>
                            <option value="approver" {{ old('role') === 'approver' ? 'selected' : '' }}>Approver - Approve service requests</option>
                            <option value="finance" {{ old('role') === 'finance' ? 'selected' : '' }}>Finance - Manage billing</option>
                        </select>
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
                        <button type="submit" class="btn btn-primary flex-1">Send Invitation</button>
                        <a href="{{ route('corporate.staff.index', $company->id) }}" class="btn btn-outline flex-1">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

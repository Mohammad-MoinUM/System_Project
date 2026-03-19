@extends('admin.layouts.app')

@section('title', $user->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="card-title text-2xl font-bold">{{ $user->name }}</h2>
                        <p class="text-base-content/60">{{ $user->email }}</p>
                    </div>
                    <span class="badge badge-lg @if($user->role === 'admin') badge-error @elseif($user->role === 'provider') badge-primary @else badge-accent @endif">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>

                <div class="divider"></div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-sm text-base-content/60">Phone</p>
                        <p class="font-semibold">{{ $user->phone ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-base-content/60">Joined</p>
                        <p class="font-semibold">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-base-content/60">City</p>
                        <p class="font-semibold">{{ $user->city ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-base-content/60">Area</p>
                        <p class="font-semibold">{{ $user->area ?? 'Not provided' }}</p>
                    </div>
                </div>

                @if($user->role === 'provider')
                    <div class="divider"></div>
                    <div>
                        <h3 class="font-semibold mb-3">Provider Details</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-base-content/60">Expertise</p>
                                <p class="font-semibold">{{ $user->expertise ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">Experience</p>
                                <p class="font-semibold">{{ $user->experience_years ?? '-' }} years</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-base-content/60">Bio</p>
                            <p class="font-semibold">{{ $user->bio ?? 'Not provided' }}</p>
                        </div>
                    </div>
                @endif

                <div class="divider"></div>

                <div class="card-actions">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">Edit User</a>
                    <button onclick="openPasswordModal()" class="btn btn-warning btn-sm">Reset Password</button>
                    <button onclick="deleteUser()" class="btn btn-error btn-sm">Delete User</button>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title font-semibold text-lg mb-4">Statistics</h3>
                
                <div class="space-y-4">
                    <div class="stat">
                        <div class="stat-title">Total Bookings</div>
                        <div class="stat-value text-primary">{{ $stats['bookings'] }}</div>
                    </div>

                    @if($user->role === 'provider')
                        <div class="stat">
                            <div class="stat-title">Services Offered</div>
                            <div class="stat-value text-accent">{{ $stats['services'] }}</div>
                        </div>
                    @endif

                    <div class="stat">
                        <div class="stat-title">Reviews Given</div>
                        <div class="stat-value text-info">{{ $stats['reviews_given'] }}</div>
                    </div>

                    @if($user->role === 'provider')
                        <div class="stat">
                            <div class="stat-title">Reviews Received</div>
                            <div class="stat-value text-warning">{{ $stats['reviews_received'] }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Password Reset Modal -->
<div id="passwordModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Reset Password for {{ $user->name }}</h3>
        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="py-4">
            @csrf
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text">New Password</span>
                </label>
                <input type="password" name="password" placeholder="Enter new password" class="input input-bordered" required minlength="8" />
                @error('password')
                    <span class="text-error text-sm p-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control mb-6">
                <label class="label">
                    <span class="label-text">Confirm Password</span>
                </label>
                <input type="password" name="password_confirmation" placeholder="Confirm password" class="input input-bordered" required minlength="8" />
            </div>

            <div class="modal-action">
                <button type="button" onclick="closePasswordModal()" class="btn">Cancel</button>
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</div>

<script>
function openPasswordModal() {
    document.getElementById('passwordModal').showModal();
}

function closePasswordModal() {
    document.getElementById('passwordModal').close();
}

function deleteUser() {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.users.destroy", $user) }}';
        form.innerHTML = '<input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection

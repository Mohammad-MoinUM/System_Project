@extends('admin.layouts.app')

@section('title', 'Create New Admin')

@section('content')
<div class="card bg-base-100 shadow-lg max-w-2xl">
    <div class="card-body">
        <h2 class="card-title text-2xl font-bold mb-6">Create New Admin User</h2>
        <p class="text-base-content/70 mb-6">Add a new administrator to manage the system. This action is restricted to existing admins only.</p>

        <form action="{{ route('admin.create-admin.store') }}" method="POST">
            @csrf

            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-semibold">Full Name</span>
                </label>
                <input type="text" name="name" placeholder="Enter admin name" 
                    class="input input-bordered @error('name') input-error @enderror" 
                    value="{{ old('name') }}" required />
                @error('name')
                    <span class="text-error text-sm p-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-semibold">Email Address</span>
                </label>
                <input type="email" name="email" placeholder="admin@example.com" 
                    class="input input-bordered @error('email') input-error @enderror" 
                    value="{{ old('email') }}" required />
                @error('email')
                    <span class="text-error text-sm p-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-semibold">Password</span>
                </label>
                <input type="password" name="password" placeholder="Enter secure password (min 8 characters)" 
                    class="input input-bordered @error('password') input-error @enderror" 
                    required minlength="8" />
                @error('password')
                    <span class="text-error text-sm p-1">{{ $message }}</span>
                @enderror
                <label class="label">
                    <span class="label-text-alt text-base-content/60">Must be at least 8 characters</span>
                </label>
            </div>

            <div class="form-control mb-6">
                <label class="label">
                    <span class="label-text font-semibold">Confirm Password</span>
                </label>
                <input type="password" name="password_confirmation" placeholder="Confirm password" 
                    class="input input-bordered @error('password_confirmation') input-error @enderror" 
                    required minlength="8" />
                @error('password_confirmation')
                    <span class="text-error text-sm p-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="alert alert-info mb-6">
                <svg class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>The new admin will have full access to the system. Share credentials securely with the administrator.</span>
            </div>

            <div class="card-actions justify-end">
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Admin</button>
            </div>
        </form>
    </div>
</div>
@endsection

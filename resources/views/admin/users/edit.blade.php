@extends('admin.layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="card bg-base-100 shadow-lg max-w-2xl">
    <div class="card-body">
        <h2 class="card-title text-2xl font-bold mb-6">Edit: {{ $user->name }}</h2>

        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-semibold">Name</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                    class="input input-bordered @error('name') input-error @enderror" required />
                @error('name')
                    <span class="text-error text-sm p-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-semibold">Email</span>
                </label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                    class="input input-bordered @error('email') input-error @enderror" required />
                @error('email')
                    <span class="text-error text-sm p-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-semibold">Role</span>
                </label>
                <select name="role" class="select select-bordered @error('role') select-error @enderror" required>
                    <option value="admin" @if($user->role === 'admin') selected @endif>Admin</option>
                    <option value="provider" @if($user->role === 'provider') selected @endif>Provider</option>
                    <option value="customer" @if($user->role === 'customer') selected @endif>Customer</option>
                </select>
                @error('role')
                    <span class="text-error text-sm p-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-semibold">Phone</span>
                </label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" 
                    class="input input-bordered" />
            </div>

            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-semibold">City</span>
                </label>
                <input type="text" name="city" value="{{ old('city', $user->city) }}" 
                    class="input input-bordered" />
            </div>

            <div class="form-control mb-6">
                <label class="label">
                    <span class="label-text font-semibold">Area</span>
                </label>
                <input type="text" name="area" value="{{ old('area', $user->area) }}" 
                    class="input input-bordered" />
            </div>

            <div class="card-actions justify-end">
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

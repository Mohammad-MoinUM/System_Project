@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-base-content mb-8">Create New Branch</h1>

        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <form action="{{ route('corporate.branches.store', $company->id) }}" method="POST">
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
                        <label for="branch_name" class="label">
                            <span class="label-text font-semibold">Branch Name *</span>
                        </label>
                        <input id="branch_name" name="branch_name" type="text" required class="input input-bordered w-full" placeholder="e.g., Downtown Branch" value="{{ old('branch_name') }}">
                    </div>

                    <div class="form-control mt-4">
                        <label for="address" class="label">
                            <span class="label-text font-semibold">Address *</span>
                        </label>
                        <input id="address" name="address" type="text" required class="input input-bordered w-full" placeholder="Street address" value="{{ old('address') }}">
                    </div>

                    <div class="form-control mt-4">
                        <label for="city" class="label">
                            <span class="label-text font-semibold">City *</span>
                        </label>
                        <input id="city" name="city" type="text" required class="input input-bordered w-full" placeholder="City" value="{{ old('city') }}">
                    </div>

                    <div class="form-control mt-4">
                        <label for="postal_code" class="label">
                            <span class="label-text font-semibold">Postal Code</span>
                        </label>
                        <input id="postal_code" name="postal_code" type="text" class="input input-bordered w-full" placeholder="Postal code" value="{{ old('postal_code') }}">
                    </div>

                    <div class="form-control mt-4">
                        <label for="phone" class="label">
                            <span class="label-text font-semibold">Phone *</span>
                        </label>
                        <input id="phone" name="phone" type="tel" required class="input input-bordered w-full" placeholder="Phone number" value="{{ old('phone') }}">
                    </div>

                    <div class="form-control mt-4">
                        <label for="manager_id" class="label">
                            <span class="label-text font-semibold">Branch Manager</span>
                        </label>
                        <select id="manager_id" name="manager_id" class="select select-bordered w-full">
                            <option value="">Select Manager</option>
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}" {{ old('manager_id') == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }} ({{ ucfirst($member->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mt-4">
                        <label for="is_active" class="label cursor-pointer">
                            <span class="label-text">Active Branch</span>
                            <input id="is_active" name="is_active" type="checkbox" class="checkbox" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        </label>
                    </div>

                    <div class="flex gap-3 mt-8">
                        <button type="submit" class="btn btn-primary flex-1">Create Branch</button>
                        <a href="{{ route('corporate.branches.index', $company->id) }}" class="btn btn-outline flex-1">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-base-content mb-8">Create Service Request</h1>

        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <form action="{{ route('corporate.requests.store', $company->id) }}" method="POST">
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
                        <label for="branch_id" class="label">
                            <span class="label-text font-semibold">Branch *</span>
                        </label>
                        <select id="branch_id" name="branch_id" required class="select select-bordered w-full">
                            <option value="">Select a branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mt-4">
                        <label for="service_id" class="label">
                            <span class="label-text font-semibold">Service *</span>
                        </label>
                        <select id="service_id" name="service_id" required class="select select-bordered w-full">
                            <option value="">Select a service</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control mt-4">
                        <label for="requested_date" class="label">
                            <span class="label-text font-semibold">Required Date *</span>
                        </label>
                        <input id="requested_date" name="requested_date" type="date" required class="input input-bordered w-full" value="{{ old('requested_date') }}">
                    </div>

                    <div class="form-control mt-4">
                        <label for="description" class="label">
                            <span class="label-text font-semibold">Description</span>
                        </label>
                        <textarea id="description" name="description" class="textarea textarea-bordered w-full h-24" placeholder="Additional details about the request">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-control mt-4">
                        <label for="budget" class="label">
                            <span class="label-text font-semibold">Budget (Optional)</span>
                        </label>
                        <input id="budget" name="budget" type="number" step="0.01" class="input input-bordered w-full" placeholder="Budget amount" value="{{ old('budget') }}">
                    </div>

                    <div class="flex gap-3 mt-8">
                        <button type="submit" class="btn btn-primary flex-1">Submit Request</button>
                        <a href="{{ route('corporate.requests.index', $company->id) }}" class="btn btn-outline flex-1">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

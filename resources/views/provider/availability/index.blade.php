@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Manage Your Availability</h1>
            <p class="text-gray-600 mt-2">Set your working hours and availability days. Customers will only see available slots when booking.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-error mb-6">
                <div class="flex">
                    <div>
                        <h3 class="font-bold">Error!</h3>
                        <div class="text-sm">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success mb-6">
                <div class="flex">
                    <div>
                        <h3 class="font-bold">Success!</h3>
                        <div class="text-sm">{{ session('success') }}</div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Availability Cards -->
        <form method="POST" action="{{ route('provider.availability.update-batch') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($availabilities as $availability)
                    <div class="card bg-base-100 shadow-md">
                        <div class="card-body">
                            <!-- Day Header with Toggle -->
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="card-title text-lg">{{ $availability->day_of_week }}</h3>
                                <div class="form-control">
                                    <label class="label cursor-pointer">
                                        <input type="checkbox" 
                                               name="availabilities[{{ $loop->index }}][is_available]" 
                                               value="1"
                                               {{ $availability->is_available ? 'checked' : '' }}
                                               class="checkbox checkbox-primary"
                                               onchange="this.form.submit()">
                                        <span class="label-text ml-2">Available</span>
                                    </label>
                                </div>
                            </div>

                            <input type="hidden" name="availabilities[{{ $loop->index }}][id]" value="{{ $availability->id }}">

                            @if ($availability->is_available)
                                <!-- Time Inputs -->
                                <div class="space-y-3">
                                    <div>
                                        <label class="label text-sm font-semibold">Start Time</label>
                                        <input type="time" 
                                               name="availabilities[{{ $loop->index }}][start_time]"
                                               value="{{ date('H:i', strtotime($availability->start_time)) }}"
                                               class="input input-bordered w-full text-sm">
                                    </div>

                                    <div>
                                        <label class="label text-sm font-semibold">End Time</label>
                                        <input type="time" 
                                               name="availabilities[{{ $loop->index }}][end_time]"
                                               value="{{ date('H:i', strtotime($availability->end_time)) }}"
                                               class="input input-bordered w-full text-sm">
                                    </div>

                                    <div class="alert alert-info p-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span class="text-xs">Customers can book in 60-minute slots within these hours</span>
                                    </div>
                                </div>
                            @else
                                <div class="badge badge-lg badge-error">Not Available</div>
                                <p class="text-sm text-gray-600 mt-2">You won't receive bookings on this day</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end mt-8">
                <button type="submit" class="btn btn-primary gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Save Availability
                </button>
            </div>
        </form>

        <!-- Info Section -->
        <div class="divider my-12">How It Works</div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="card bg-blue-50 border border-blue-200">
                <div class="card-body">
                    <div class="text-3xl mb-2">📅</div>
                    <h4 class="card-title text-lg">Set Your Hours</h4>
                    <p class="text-sm">Define working hours for each day of the week</p>
                </div>
            </div>

            <div class="card bg-green-50 border border-green-200">
                <div class="card-body">
                    <div class="text-3xl mb-2">🚫</div>
                    <h4 class="card-title text-lg">Auto Conflict Check</h4>
                    <p class="text-sm">System prevents double bookings automatically</p>
                </div>
            </div>

            <div class="card bg-purple-50 border border-purple-200">
                <div class="card-body">
                    <div class="text-3xl mb-2">✅</div>
                    <h4 class="card-title text-lg">Smart Slots</h4>
                    <p class="text-sm">Customers see only available time slots</p>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-8">
            <a href="{{ route('provider.dashboard') }}" class="btn btn-ghost">
                ← Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script>
    // Auto-submit when toggling availability
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
</script>
@endsection

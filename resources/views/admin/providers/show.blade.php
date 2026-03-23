@extends('admin.layouts.app')

@section('title', $provider->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="card-title text-2xl font-bold">{{ $provider->name }}</h2>
                        <p class="text-base-content/60">{{ $provider->email }}</p>
                    </div>
                    <span class="badge badge-lg badge-warning">{{ ucfirst($provider->verification_status) }}</span>
                </div>

                <div class="divider"></div>

                <!-- Personal Information -->
                <div class="mb-6">
                    <h3 class="font-semibold text-lg mb-4">Personal Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-base-content/60">Phone</p>
                            <p class="font-semibold">{{ $provider->phone ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">NID Number</p>
                            <p class="font-semibold">{{ $provider->nid_number ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">City</p>
                            <p class="font-semibold">{{ $provider->city ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">Area</p>
                            <p class="font-semibold">{{ $provider->area ?? 'Not provided' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="mb-6">
                    <h3 class="font-semibold text-lg mb-4">Professional Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-base-content/60">Expertise</p>
                            <p class="font-semibold">{{ $provider->expertise ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">Experience</p>
                            <p class="font-semibold">{{ $provider->experience_years ?? 'N/A' }} years</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-base-content/60">Education</p>
                            <p class="font-semibold">{{ $provider->education ?? 'Not provided' }}</p>
                        </div>
                        @if($provider->institution)
                            <div class="col-span-2">
                                <p class="text-sm text-base-content/60">Institution</p>
                                <p class="font-semibold">{{ $provider->institution }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Bio -->
                @if($provider->bio)
                    <div class="mb-6">
                        <h3 class="font-semibold text-lg mb-3">Biography</h3>
                        <p class="text-base-content/80 bg-base-200 p-4 rounded-lg">{{ $provider->bio }}</p>
                    </div>
                @endif

                <!-- Certifications -->
                @if($provider->certifications && count($provider->certifications) > 0)
                    <div class="mb-6">
                        <h3 class="font-semibold text-lg mb-3">Certifications</h3>
                        <div class="space-y-2">
                            @foreach($provider->certifications as $cert)
                                <div class="badge badge-lg badge-accent">{{ $cert }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Services Offered -->
                @if($provider->services_offered && count($provider->services_offered) > 0)
                    <div class="mb-6">
                        <h3 class="font-semibold text-lg mb-3">Services Offered</h3>
                        <div class="space-y-2">
                            @foreach($provider->services_offered as $service)
                                <div class="badge badge-primary">{{ $service }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="divider"></div>

                <!-- Action Buttons -->
                <div class="card-actions justify-end gap-3">
                    <form action="{{ route('admin.providers.approve', $provider) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Approve this provider?')">
                            ✓ Approve Provider
                        </button>
                    </form>
                    
                    <button onclick="openRejectModal()" class="btn btn-error">
                        ✗ Reject Provider
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Profile Photo -->
        @if($provider->photo)
            <div class="card bg-base-100 shadow">
                <figure>
                    <img src="{{ asset('storage/' . $provider->photo) }}" alt="{{ $provider->name }}" class="w-full h-64 object-cover">
                </figure>
                <div class="card-body">
                    <p class="text-sm text-center text-base-content/60">Profile Photo</p>
                </div>
            </div>
        @endif

        <!-- Statistics -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="card-title font-semibold mb-4">Statistics</h3>
                
                <div class="space-y-3">
                    <div class="stat">
                        <div class="stat-title text-sm">Services</div>
                        <div class="stat-value text-2xl">{{ $stats['services'] }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title text-sm">Bookings</div>
                        <div class="stat-value text-2xl">{{ $stats['bookings'] }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title text-sm">Reviews</div>
                        <div class="stat-value text-2xl">{{ $stats['reviews'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Details -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="card-title font-semibold mb-4">Account Details</h3>
                
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-base-content/60">Registered</p>
                        <p class="font-semibold">{{ $provider->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-base-content/60">Status</p>
                        <span class="badge badge-lg badge-warning">{{ ucfirst($provider->verification_status) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Reject Provider</h3>
        <p class="text-base-content/70 mb-6">Provide a reason for rejection. This will be shown to the provider.</p>
        
        <form action="{{ route('admin.providers.reject', $provider) }}" method="POST">
            @csrf
            
            <div class="form-control mb-6">
                <label class="label">
                    <span class="label-text font-semibold">Reason for Rejection</span>
                </label>
                <textarea name="rejection_reason" placeholder="e.g., Incomplete documentation, Invalid credentials, etc." 
                    class="textarea textarea-bordered h-24 @error('rejection_reason') textarea-error @enderror" 
                    required></textarea>
                @error('rejection_reason')
                    <span class="text-error text-sm p-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="modal-action">
                <button type="button" onclick="closeRejectModal()" class="btn">Cancel</button>
                <button type="submit" class="btn btn-error">Confirm Rejection</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</div>

<script>
function openRejectModal() {
    document.getElementById('rejectModal').showModal();
}

function closeRejectModal() {
    document.getElementById('rejectModal').close();
}
</script>
@endsection

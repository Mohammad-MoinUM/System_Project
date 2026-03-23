@extends('layouts.app')

@section('hideNavbar', true)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-base-200 to-base-300 flex items-center justify-center px-4">
    <div class="w-full max-w-2xl">
        <div class="card bg-base-100 shadow-2xl">
            <div class="card-body text-center">
                <!-- Pending Icon -->
                <div class="mb-8 flex justify-center">
                    <div class="relative">
                        <div class="w-24 h-24 bg-warning/20 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-warning animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <h1 class="text-3xl md:text-4xl font-bold text-base-content mb-3">
                    Verification Pending
                </h1>

                <p class="text-base-content/70 mb-8 text-lg">
                    Thank you for completing your provider profile! 🎉
                </p>

                <div class="bg-info/10 border-l-4 border-info p-6 rounded-lg mb-8 text-left">
                    <h3 class="font-semibold text-info mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
                        </svg>
                        What Happens Next?
                    </h3>
                    <ul class="space-y-3 text-base-content/80">
                        <li class="flex items-start">
                            <span class="badge badge-sm badge-primary mr-3 mt-0.5">1</span>
                            <span class="pt-1">Our admin team will review your profile, qualifications, and credentials</span>
                        </li>
                        <li class="flex items-start">
                            <span class="badge badge-sm badge-primary mr-3 mt-0.5">2</span>
                            <span class="pt-1">We verify your information for trust and quality standards</span>
                        </li>
                        <li class="flex items-start">
                            <span class="badge badge-sm badge-primary mr-3 mt-0.5">3</span>
                            <span class="pt-1">You'll receive notification once your profile is approved</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-success/10 border-l-4 border-success p-6 rounded-lg mb-8 text-left">
                    <h3 class="font-semibold text-success mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Profile Information Verified ✓
                    </h3>
                    <p class="text-base-content/80">
                        Your profile information has been successfully received. We will contact you within 2-3 business days with updates about your verification status.
                    </p>
                </div>

                <div class="divider"></div>

                <div class="stats stats-vertical lg:stats-horizontal shadow w-full bg-base-200 mb-8">
                    <div class="stat">
                        <div class="stat-title">Name</div>
                        <div class="stat-value text-lg">{{ $user->name }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title">Email</div>
                        <div class="stat-value text-lg break-all">{{ $user->email }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title">Status</div>
                        <div class="stat-value text-lg text-warning">Pending</div>
                    </div>
                </div>

                <div class="card-actions justify-center gap-4">
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-lg">
                            Logout for Now
                        </button>
                    </form>
                    <a href="{{ route('home') }}" class="btn btn-ghost btn-lg">
                        Back to Home
                    </a>
                </div>

                <p class="text-sm text-base-content/60 mt-6">
                    Have questions? Contact our support team at support@haalchaal.com
                </p>
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-8 card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="card-title text-lg font-semibold mb-4">📋 Tips for Faster Approval</h3>
                <ul class="space-y-2 text-base-content/80">
                    <li class="flex items-start">
                        <span class="text-primary mr-3">•</span>
                        <span>Complete all required fields in your profile</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-primary mr-3">•</span>
                        <span>Ensure your NID number is correct and verifiable</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-primary mr-3">•</span>
                        <span>Add relevant certifications and credentials</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-primary mr-3">•</span>
                        <span>Provide a clear, professional profile picture</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-primary mr-3">•</span>
                        <span>Keep your contact information up to date</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('hideNavbar', true)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-base-200 to-base-300 flex items-center justify-center px-4">
    <div class="w-full max-w-2xl">
        <div class="card bg-base-100 shadow-2xl">
            <div class="card-body text-center">
                <!-- Rejected Icon -->
                <div class="mb-8 flex justify-center">
                    <div class="relative">
                        <div class="w-24 h-24 bg-error/20 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <h1 class="text-3xl md:text-4xl font-bold text-base-content mb-3">
                    Verification Not Approved
                </h1>

                <p class="text-base-content/70 mb-8 text-lg">
                    Unfortunately, your provider profile was not approved at this time.
                </p>

                @if($user->rejection_reason)
                    <div class="alert alert-error shadow-lg mb-8">
                        <svg class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0-6a4 4 0 00-4 4m0 0a4 4 0 004 4m0-4a4 4 0 00-4-4m0 0a4 4 0 004 4" />
                        </svg>
                        <div class="text-left">
                            <h3 class="font-semibold">Reason for Rejection</h3>
                            <div class="text-sm mt-2">{{ $user->rejection_reason }}</div>
                        </div>
                    </div>
                @endif

                <div class="bg-info/10 border-l-4 border-info p-6 rounded-lg mb-8 text-left">
                    <h3 class="font-semibold text-info mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
                        </svg>
                        What Can You Do?
                    </h3>
                    <ul class="space-y-3 text-base-content/80">
                        <li class="flex items-start">
                            <span class="text-success mr-3">✓</span>
                            <span class="pt-1">Review the rejection reason carefully</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-success mr-3">✓</span>
                            <span class="pt-1">Update your profile with correct information</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-success mr-3">✓</span>
                            <span class="pt-1">Ensure all documents and credentials are accurate</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-success mr-3">✓</span>
                            <span class="pt-1">Contact support for clarification if needed</span>
                        </li>
                    </ul>
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
                        <div class="stat-value text-lg text-error">Rejected</div>
                    </div>
                </div>

                <div class="card-actions justify-center gap-4">
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-error btn-lg">
                            Logout
                        </button>
                    </form>
                    <a href="{{ route('home') }}" class="btn btn-ghost btn-lg">
                        Back to Home
                    </a>
                </div>

                <p class="text-sm text-base-content/60 mt-6">
                    Need help? Contact our support team at support@haalchaal.com
                </p>
            </div>
        </div>

        <!-- Support Info -->
        <div class="mt-8 card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="card-title text-lg font-semibold mb-4">📞 Support</h3>
                <p class="text-base-content/80 mb-4">
                    If you believe this was a mistake or have questions about the rejection, please contact our support team.
                </p>
                <div class="space-y-2 text-base-content/80">
                    <p><strong>Email:</strong> support@haalchaal.com</p>
                    <p><strong>Phone:</strong> +880 1234 567890</p>
                    <p><strong>Hours:</strong> Monday - Friday, 9 AM - 6 PM</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

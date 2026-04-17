@extends('layouts.app')

@section('content')
<section class="min-h-[70vh] flex items-center justify-center py-10">
    <div class="card w-full max-w-lg bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h1 class="text-2xl font-bold">Verify Your Email</h1>
            <p class="text-base-content/70">
                Please verify your email address to continue. Check your inbox for the verification link.
            </p>

            @if (session('success'))
                <div class="alert alert-success mt-3">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info mt-3">
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if (!empty($devVerificationUrl))
                <div class="alert alert-warning mt-3">
                    <div>
                        <p class="font-semibold">Local development mode detected</p>
                        <p class="text-sm">Your mailer is set to <code>log</code>, so emails are written to <code>storage/logs/laravel.log</code> instead of being sent.</p>
                        <a href="{{ $devVerificationUrl }}" class="btn btn-sm btn-secondary mt-2">Verify Email Now (Local)</a>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-primary w-full">Resend Verification Email</button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-ghost w-full">Sign Out</button>
            </form>
        </div>
    </div>
</section>
@endsection

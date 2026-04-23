@extends('layouts.app')

@section('content')
<section class="min-h-[70vh] flex items-center justify-center py-10">
    <div class="card w-full max-w-xl bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h1 class="text-2xl font-bold">Accept Staff Invitation</h1>
            <p class="text-base-content/70">
                You have been invited to join
                <span class="font-semibold">{{ $invitation->company->name }}</span>
                as
                <span class="font-semibold">{{ ucfirst($invitation->role) }}</span>.
            </p>

            <div class="rounded-lg bg-base-200 px-4 py-3 text-sm">
                <p><span class="font-medium">Email:</span> {{ $invitation->email }}</p>
                <p><span class="font-medium">Expires:</span> {{ $invitation->expires_at->format('M d, Y h:i A') }}</p>
            </div>

            <form method="POST" action="{{ route('staff-invitations.accept', $invitation->token) }}" class="space-y-4">
                @csrf

                @guest
                    @if($hasAccount)
                        <div class="alert alert-info">
                            An account already exists for this email. Please sign in first, then open this invitation link again.
                        </div>
                        <a href="{{ route('login') }}" class="btn btn-primary w-full">Sign In</a>
                    @else
                        <div>
                            <label class="label" for="name">
                                <span class="label-text">Full Name</span>
                            </label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                class="input input-bordered w-full @error('name') input-error @enderror"
                                value="{{ old('name') }}"
                                required
                            >
                            @error('name')<p class="text-error text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="label" for="password">
                                <span class="label-text">Password</span>
                            </label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="input input-bordered w-full @error('password') input-error @enderror"
                                required
                            >
                            @error('password')<p class="text-error text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="label" for="password_confirmation">
                                <span class="label-text">Confirm Password</span>
                            </label>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="input input-bordered w-full"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary w-full">Create Account and Accept</button>
                    @endif
                @else
                    <div class="alert alert-info">
                        You are signed in as {{ auth()->user()->email }}. Click below to accept this invitation.
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Accept Invitation</button>
                @endguest
            </form>
        </div>
    </div>
</section>
@endsection

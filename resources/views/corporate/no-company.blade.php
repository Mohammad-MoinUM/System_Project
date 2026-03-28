@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="text-center">
        <div class="mb-8">
            <svg class="w-16 h-16 mx-auto text-base-content/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m0 0l8-4m8 4v10l-8 4m-8-4l8 4m0-10l-8 4m8-4l8-4"></path>
            </svg>
            <h1 class="text-3xl font-bold text-base-content mb-4">No Company Found</h1>
            <p class="text-base-content/70 mb-8">You are not currently part of any company. Register a new company to get started.</p>
        </div>

        <div class="flex gap-4 justify-center">
            <a href="{{ route('corporate.register') }}" class="btn btn-primary">
                Register New Company
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline">
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection

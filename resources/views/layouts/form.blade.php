<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<script>try{document.documentElement.setAttribute('data-theme',localStorage.getItem('theme')||'light')}catch(e){}</script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Complete Your Profile') - {{ config('app.name', 'Haalchaal') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="bg-base-200 min-h-screen font-sans antialiased">
    <div class="max-w-3xl mx-auto px-6 py-10">

        {{-- Progress Bar --}}
        @hasSection('progress')
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-base-content/60">Profile Completion</span>
                <span class="text-sm font-bold text-base-content">@yield('progress')%</span>
            </div>
            <div class="w-full bg-base-300 rounded-full h-2.5 overflow-hidden">
                <div class="bg-base-content h-2.5 rounded-full transition-all duration-500 ease-out"
                     style="width: @yield('progress')%"></div>
            </div>
        </div>
        @endif

        {{-- Step Indicators (child views define their own steps) --}}
        @yield('steps')

        {{-- Form Card --}}
        <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm p-8 sm:p-10">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div role="alert" class="alert alert-success mb-6">
                    <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div role="alert" class="alert alert-error mb-6">
                    <x-heroicon-o-x-circle class="w-5 h-5 shrink-0" />
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="alert alert-warning mb-6">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 shrink-0" />
                    <div>
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Page Content --}}
            @yield('content')
        </div>

        {{-- Footer Buttons --}}
        @yield('buttons')

    </div>

    @stack('scripts')
</body>
</html>
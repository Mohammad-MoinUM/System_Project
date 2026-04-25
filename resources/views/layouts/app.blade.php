<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<script>try{document.documentElement.setAttribute('data-theme',localStorage.getItem('theme')||'light')}catch(e){}</script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Haalchaal') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-base-200 min-h-screen">
    @hasSection('hideNavbar')
    @else
        <x-navbar />
    @endif

    <main class="container mx-auto px-4 py-6 sm:px-6 sm:py-8 pb-24 lg:pb-8">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
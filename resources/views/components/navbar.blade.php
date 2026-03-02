@php
    $isProvider = request()->routeIs('provider.*');
    $isCustomer = request()->routeIs('customer.*');
    $isAuthenticated = auth()->check();

    $providerTabs = [
        ['label' => 'Overview', 'route' => 'provider.dashboard'],
        ['label' => 'Jobs', 'route' => 'provider.jobs'],
        ['label' => 'Reviews', 'route' => 'provider.reviews'],
        ['label' => 'Earnings', 'route' => 'provider.earnings'],
        ['label' => 'Settings', 'route' => 'provider.settings'],
    ];

    $CustomerTabs = [
        ['label' => 'Overview', 'route' => 'customer.dashboard'],
        ['label' => 'Browse', 'route' => 'customer.browse'],
        ['label' => 'History', 'route' => 'customer.history'],
    ];

    $mainTabs = [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'Services', 'route' => 'services'],
        ['label' => 'How It Works', 'route' => 'how-it-works'],
        ['label' => 'About', 'route' => 'about'],
        ['label' => 'Contact', 'route' => 'contact'],
    ];

    if ($isProvider) {
        $tabs = $providerTabs;
    } elseif ($isCustomer) {
        $tabs = $CustomerTabs;
    } else {
        $tabs = $mainTabs;
    }
    $currencyOptions = config('currencies.options', []);
    $currentCurrency = session('currency', config('currencies.default', 'BDT'));
@endphp

<nav class="sticky top-0 z-50 border-b border-base-200 bg-base-100/80 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-semibold text-base-content">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z" />
                    </svg>
                </span>
                <span>{{ config('app.name', 'HaalChaal') }}</span>
            </a>

            @if (count($tabs) > 0)
                <div class="hidden lg:flex items-center">
                    <div class="tabs tabs-boxed bg-base-200/60 p-1">
                        @foreach ($tabs as $tab)
                            @if (Route::has($tab['route']))
                                <a href="{{ route($tab['route']) }}"
                                   class="tab {{ request()->routeIs($tab['route']) ? 'tab-active' : '' }}">
                                    {{ $tab['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex items-center gap-2">
                @if ($isAuthenticated)
                    <form method="POST" action="{{ route('currency.set') }}" class="hidden md:block">
                        @csrf
                        <select name="currency" class="select select-bordered select-sm" onchange="this.form.submit()">
                            @foreach ($currencyOptions as $code => $option)
                                <option value="{{ $code }}" {{ $code === $currentCurrency ? 'selected' : '' }}>
                                    {{ $option['label'] ?? $code }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
                @if ($isAuthenticated)
                    <a href="{{ route('profile') }}" class="btn btn-ghost btn-sm" aria-label="Profile">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm" aria-label="Notifications">
                        <div class="indicator">
                            <span class="indicator-item badge badge-xs badge-primary"></span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a3 3 0 0 0 6 0" />
                            </svg>
                        </div>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Get started</a>
                @endif
            </div>
        </div>

        <div class="pb-3 lg:hidden">
            @if ($isAuthenticated)
                <form method="POST" action="{{ route('currency.set') }}" class="mb-2">
                    @csrf
                    <select name="currency" class="select select-bordered select-sm w-full" onchange="this.form.submit()">
                        @foreach ($currencyOptions as $code => $option)
                            <option value="{{ $code }}" {{ $code === $currentCurrency ? 'selected' : '' }}>
                                {{ $option['label'] ?? $code }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
            @if (count($tabs) > 0)
                <div class="tabs tabs-boxed bg-base-200/60 p-1 w-full overflow-x-auto">
                    @foreach ($tabs as $tab)
                        @if (Route::has($tab['route']))
                            <a href="{{ route($tab['route']) }}"
                               class="tab {{ request()->routeIs($tab['route']) ? 'tab-active' : '' }}">
                                {{ $tab['label'] }}
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</nav>


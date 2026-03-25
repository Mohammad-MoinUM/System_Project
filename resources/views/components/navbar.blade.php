@php
    $isProvider = request()->routeIs('provider.*');
    $isCustomer = request()->routeIs('customer.*');
    $isAuthenticated = auth()->check();

    $providerTabs = [
        ['route' => 'provider.dashboard', 'label' => 'Dashboard'],
        ['route' => 'provider.jobs', 'label' => 'Jobs'],
        ['route' => 'provider.services.index', 'label' => 'Services'],
        ['route' => 'provider.availability.index', 'label' => 'Availability'],
        ['route' => 'provider.earnings', 'label' => 'Earnings'],
        ['route' => 'provider.reviews', 'label' => 'Reviews'],
        ['route' => 'provider.schedule', 'label' => 'Schedule'],
        ['route' => 'provider.analytics', 'label' => 'Analytics'],
    ];

    $CustomerTabs = [
        ['route' => 'customer.dashboard', 'label' => 'Dashboard'],
        ['route' => 'customer.browse', 'label' => 'Browse'],
        ['route' => 'customer.history', 'label' => 'My Bookings'],
        ['route' => 'customer.saved', 'label' => 'Saved'],
    ];

    $mainTabs = [
        ['route' => 'home', 'label' => 'Home'],
        ['route' => 'services', 'label' => 'Services'],
        ['route' => 'how-it-works', 'label' => 'How It Works'],
        ['route' => 'about', 'label' => 'About'],
        ['route' => 'contact', 'label' => 'Contact'],
    ];

    if ($isProvider) {
        $tabs = $providerTabs;
    } elseif ($isCustomer) {
        $tabs = $CustomerTabs;
    } else {
        $tabs = $mainTabs;
    }
@endphp

<nav class="sticky top-0 z-50 border-b border-base-200 bg-base-100/80 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-semibold text-base-content">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <x-heroicon-s-briefcase class="w-5 h-5" />
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
                {{-- Theme Switcher --}}
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-sm" aria-label="Change theme">
                        <x-heroicon-o-swatch class="w-5 h-5" />
                    </label>
                    <ul tabindex="0" class="dropdown-content z-[100] mt-3 w-44 rounded-xl bg-base-100 shadow-xl border border-base-200 py-2 max-h-72 overflow-y-auto">
                        @foreach (['light', 'dark', 'cupcake', 'bumblebee', 'emerald', 'corporate', 'synthwave', 'retro', 'cyberpunk', 'valentine', 'halloween', 'garden', 'forest', 'aqua', 'lofi', 'pastel', 'fantasy', 'wireframe', 'luxury', 'dracula', 'cmyk', 'autumn', 'business', 'acid', 'lemonade', 'night', 'coffee', 'winter', 'dim', 'nord', 'sunset'] as $theme)
                            <li>
                                <button onclick="setTheme('{{ $theme }}')"
                                    class="flex items-center gap-3 w-full px-4 py-2 text-sm text-base-content hover:bg-base-200 transition-colors capitalize"
                                    data-theme-btn="{{ $theme }}">
                                    <span class="flex gap-0.5">
                                        <span data-theme="{{ $theme }}" class="w-2 h-4 rounded-sm bg-primary"></span>
                                        <span data-theme="{{ $theme }}" class="w-2 h-4 rounded-sm bg-secondary"></span>
                                        <span data-theme="{{ $theme }}" class="w-2 h-4 rounded-sm bg-accent"></span>
                                    </span>
                                    {{ $theme }}
                                    <span class="ml-auto text-primary hidden" data-theme-check="{{ $theme }}">&#10003;</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if ($isAuthenticated)
                    @php
                        $unreadCount = auth()->user()->unreadNotifications()->count();
                        $recentNotifications = auth()->user()->notifications()->take(5)->get();
                    @endphp

                    {{-- Profile Dropdown --}}
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm" aria-label="Profile">
                            <x-heroicon-o-user class="w-5 h-5" />
                        </label>
                        <ul tabindex="0" class="dropdown-content z-[100] mt-3 w-52 rounded-xl bg-base-100 shadow-xl border border-base-200 py-2">
                            <li>
                                <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-base-content hover:bg-base-200 transition-colors">
                                    <x-heroicon-o-user-circle class="w-4 h-4 text-base-content/50" />
                                    View Profile
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-base-content hover:bg-base-200 transition-colors">
                                    <x-heroicon-o-pencil-square class="w-4 h-4 text-base-content/50" />
                                    Edit Profile
                                </a>
                            </li>
                            <li class="border-t border-base-200 mt-1 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-error hover:bg-base-200 transition-colors">
                                        <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                                        Log Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>

                    {{-- Notification Bell with Dropdown --}}
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm" aria-label="Notifications">
                            <div class="indicator">
                                @if($unreadCount > 0)
                                    <span class="indicator-item badge badge-xs badge-primary">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                                @endif
                                <x-heroicon-o-bell class="w-5 h-5" />
                            </div>
                        </label>
                        <div tabindex="0" class="dropdown-content z-[100] mt-3 w-80 rounded-xl bg-base-100 shadow-xl border border-base-200">
                            <div class="p-3 border-b border-base-200 flex items-center justify-between">
                                <span class="font-semibold text-sm text-base-content">Notifications</span>
                                @if($unreadCount > 0)
                                    <form method="POST" action="{{ route('notifications.readAll') }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-primary hover:underline">Mark all read</button>
                                    </form>
                                @endif
                            </div>
                            <ul class="max-h-72 overflow-y-auto">
                                @forelse($recentNotifications as $notif)
                                    @php $nd = $notif->data; @endphp
                                    <li class="px-3 py-2.5 border-b border-base-200/50 last:border-0 {{ is_null($notif->read_at) ? 'bg-primary/5' : '' }}">
                                        <p class="text-sm font-medium text-base-content">{{ $nd['title'] ?? 'Notification' }}</p>
                                        <p class="text-xs text-base-content/60 mt-0.5">{{ Str::limit($nd['message'] ?? '', 60) }}</p>
                                        <span class="text-xs text-base-content/40">{{ $notif->created_at->diffForHumans() }}</span>
                                    </li>
                                @empty
                                    <li class="px-3 py-6 text-center text-sm text-base-content/40">No notifications</li>
                                @endforelse
                            </ul>
                            <div class="p-2 border-t border-base-200 text-center">
                                <a href="{{ route('notifications.index') }}" class="text-xs text-primary font-medium hover:underline">View all notifications</a>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Get started</a>
                @endif
            </div>
        </div>

        <div class="pb-3 lg:hidden">
           
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

<script>
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    // Update checkmarks
    document.querySelectorAll('[data-theme-check]').forEach(el => el.classList.add('hidden'));
    const active = document.querySelector(`[data-theme-check="${theme}"]`);
    if (active) active.classList.remove('hidden');
}
// Mark current theme on load
document.addEventListener('DOMContentLoaded', function() {
    const current = localStorage.getItem('theme') || 'light';
    const active = document.querySelector(`[data-theme-check="${current}"]`);
    if (active) active.classList.remove('hidden');
});
</script>


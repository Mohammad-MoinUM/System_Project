@extends('layouts.app')

@section('content')

<section class="bg-base-200">
  <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Settings</h1>
    <p class="mt-2 text-base-content/60">Manage your account preferences.</p>

    @if(session('success'))
      <div class="alert alert-success mt-4">{{ session('success') }}</div>
    @endif

    <div class="mt-8 space-y-6">
      {{-- Appearance --}}
      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h2 class="text-xl font-bold text-base-content">Appearance</h2>
        <p class="mt-1 text-sm text-base-content/60">Choose a theme for the interface.</p>
        <div class="mt-4 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
          @foreach (['light', 'dark', 'cupcake', 'bumblebee', 'emerald', 'corporate', 'synthwave', 'retro', 'cyberpunk', 'valentine', 'halloween', 'garden', 'forest', 'aqua', 'lofi', 'pastel', 'fantasy', 'wireframe', 'luxury', 'dracula', 'cmyk', 'autumn', 'business', 'acid', 'lemonade', 'night', 'coffee', 'winter', 'dim', 'nord', 'sunset'] as $theme)
            <button onclick="setTheme('{{ $theme }}')"
              data-theme="{{ $theme }}"
              class="rounded-lg border-2 border-base-300 p-2 text-center transition hover:scale-105 focus:outline-none"
              data-settings-theme="{{ $theme }}">
              <div class="flex justify-center gap-0.5 mb-1">
                <span class="w-3 h-5 rounded-sm bg-primary"></span>
                <span class="w-3 h-5 rounded-sm bg-secondary"></span>
                <span class="w-3 h-5 rounded-sm bg-accent"></span>
              </div>
              <span class="text-xs font-medium capitalize text-base-content">{{ $theme }}</span>
            </button>
          @endforeach
        </div>
      </div>

      {{-- Profile --}}
      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h2 class="text-xl font-bold text-base-content">Profile</h2>
        <p class="mt-1 text-sm text-base-content/60">Update your personal information and photo.</p>
        <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm mt-4">Edit Profile</a>
      </div>

      {{-- Notifications --}}
      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h2 class="text-xl font-bold text-base-content">Notifications</h2>
        <p class="mt-1 text-sm text-base-content/60">View and manage your notifications.</p>
        <a href="{{ route('notifications.index') }}" class="btn btn-outline btn-sm mt-4">View Notifications</a>
      </div>

      {{-- Currency --}}
      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h2 class="text-xl font-bold text-base-content">Currency</h2>
        <p class="mt-1 text-sm text-base-content/60">Choose your preferred display currency.</p>
        <form method="POST" action="{{ route('currency.set') }}" class="mt-4">
          @csrf
          @php
            $currencyOptions = config('currencies.options', []);
            $currency = session('currency', config('currencies.default', 'BDT'));
          @endphp
          <select name="currency" onchange="this.form.submit()" class="select select-bordered select-sm">
            @foreach ($currencyOptions as $code => $meta)
              <option value="{{ $code }}" {{ $currency === $code ? 'selected' : '' }}>
                {{ $meta['symbol'] }} {{ $code }}
              </option>
            @endforeach
          </select>
        </form>
      </div>

      {{-- Account --}}
      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h2 class="text-xl font-bold text-base-content">Account</h2>
        <p class="mt-1 text-sm text-base-content/60">Manage your account and security settings.</p>
        <form method="POST" action="{{ route('logout') }}" class="mt-4">
          @csrf
          <button type="submit" class="btn btn-error btn-sm">Log Out</button>
        </form>
      </div>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    highlightActiveTheme(theme);
}
function highlightActiveTheme(theme) {
    document.querySelectorAll('[data-settings-theme]').forEach(el => {
        el.classList.toggle('ring-2', el.dataset.settingsTheme === theme);
        el.classList.toggle('ring-primary', el.dataset.settingsTheme === theme);
        el.classList.toggle('border-primary', el.dataset.settingsTheme === theme);
    });
    document.querySelectorAll('[data-theme-check]').forEach(el => el.classList.add('hidden'));
    const check = document.querySelector(`[data-theme-check="${theme}"]`);
    if (check) check.classList.remove('hidden');
}
document.addEventListener('DOMContentLoaded', function() {
    highlightActiveTheme(localStorage.getItem('theme') || 'light');
});
</script>
@endpush

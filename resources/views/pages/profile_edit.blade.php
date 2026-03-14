@extends('layouts.app')

@section('title', 'Edit Profile')
@section('content')

@php $isProvider = $user->role === 'provider'; @endphp

<section class="bg-base-100">
  <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">

    <div class="mb-8">
      <h2 class="text-2xl font-bold text-base-content">Edit Profile</h2>
      <p class="mt-1 text-base-content/60">
        {{ $isProvider ? 'Keep your professional profile up to date so customers can find you.' : 'Update your personal information to get the best experience.' }}
      </p>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
      @csrf
      @method('PUT')

      {{-- Photo --}}
      <div class="flex flex-col items-center mb-2">
        <label for="photo" class="cursor-pointer group">
          <div class="w-20 h-20 rounded-full border-2 border-dashed border-base-300 group-hover:border-primary/40 flex items-center justify-center overflow-hidden transition-colors"
               id="photo-preview-container">
            @if($user->photo)
              <img src="{{ asset('storage/' . $user->photo) }}" class="w-full h-full object-cover" alt="Profile" id="photo-preview" />
            @else
              <x-heroicon-o-camera class="w-8 h-8 text-base-content/30 group-hover:text-base-content/50" />
            @endif
          </div>
          <input type="file" id="photo" name="photo" accept="image/*" class="hidden" onchange="previewPhoto(this)">
        </label>
        <span class="text-xs text-base-content/40 mt-2">Click to change photo</span>
        @error('photo')
          <span class="text-error text-xs mt-1">{{ $message }}</span>
        @enderror
      </div>

      {{-- ═══ Personal Info (Both roles) ═══ --}}
      <div class="divider text-xs text-base-content/40 uppercase tracking-wider">Personal Information</div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="first_name" class="text-sm font-semibold text-base-content mb-1 block">First Name <span class="text-error">*</span></label>
          <input type="text" id="first_name" name="first_name"
                 value="{{ old('first_name', $user->first_name) }}"
                 class="input input-bordered w-full @error('first_name') input-error @enderror" required>
          @error('first_name')
            <span class="text-error text-xs mt-1">{{ $message }}</span>
          @enderror
        </div>
        <div>
          <label for="last_name" class="text-sm font-semibold text-base-content mb-1 block">Last Name <span class="text-error">*</span></label>
          <input type="text" id="last_name" name="last_name"
                 value="{{ old('last_name', $user->last_name) }}"
                 class="input input-bordered w-full @error('last_name') input-error @enderror" required>
          @error('last_name')
            <span class="text-error text-xs mt-1">{{ $message }}</span>
          @enderror
        </div>
      </div>

      {{-- ═══ Contact (Both roles, provider gets alt phone) ═══ --}}
      <div class="divider text-xs text-base-content/40 uppercase tracking-wider">Contact</div>

      <div class="grid grid-cols-1 {{ $isProvider ? 'sm:grid-cols-2' : '' }} gap-4">
        <div>
          <label for="phone" class="text-sm font-semibold text-base-content mb-1 block">Phone <span class="text-error">*</span></label>
          <input type="tel" id="phone" name="phone"
                 value="{{ old('phone', $user->phone) }}"
                 placeholder="+8801XXXXXXXXX"
                 class="input input-bordered w-full @error('phone') input-error @enderror" required>
          @error('phone')
            <span class="text-error text-xs mt-1">{{ $message }}</span>
          @enderror
        </div>
        @if($isProvider)
          <div>
            <label for="alt_phone" class="text-sm font-semibold text-base-content mb-1 block">Alt. Phone</label>
            <input type="tel" id="alt_phone" name="alt_phone"
                   value="{{ old('alt_phone', $user->alt_phone) }}"
                   placeholder="+8801XXXXXXXXX"
                   class="input input-bordered w-full @error('alt_phone') input-error @enderror">
            @error('alt_phone')
              <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
          </div>
        @endif
      </div>

      {{-- ═══ Location (Both roles) ═══ --}}
      <div class="divider text-xs text-base-content/40 uppercase tracking-wider">Location</div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="city" class="text-sm font-semibold text-base-content mb-1 block">City</label>
          <input type="text" id="city" name="city"
                 value="{{ old('city', $user->city) }}"
                 class="input input-bordered w-full @error('city') input-error @enderror">
          @error('city')
            <span class="text-error text-xs mt-1">{{ $message }}</span>
          @enderror
        </div>
        <div>
          <label for="area" class="text-sm font-semibold text-base-content mb-1 block">Area</label>
          <input type="text" id="area" name="area"
                 value="{{ old('area', $user->area) }}"
                 class="input input-bordered w-full @error('area') input-error @enderror">
          @error('area')
            <span class="text-error text-xs mt-1">{{ $message }}</span>
          @enderror
        </div>
      </div>

      {{-- ═══ Provider-Only: Professional Details ═══ --}}
      @if($isProvider)
        <div class="divider text-xs text-base-content/40 uppercase tracking-wider">Professional Details</div>

        <div>
          <label for="bio" class="text-sm font-semibold text-base-content mb-1 block">Bio</label>
          <textarea id="bio" name="bio" rows="3"
                    class="textarea textarea-bordered w-full @error('bio') textarea-error @enderror"
                    placeholder="Tell customers about yourself, your approach, and what makes you stand out...">{{ old('bio', $user->bio) }}</textarea>
          <span class="text-xs text-base-content/40 mt-1 block">This is displayed on your public provider profile.</span>
          @error('bio')
            <span class="text-error text-xs mt-1">{{ $message }}</span>
          @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="expertise" class="text-sm font-semibold text-base-content mb-1 block">Expertise</label>
            <input type="text" id="expertise" name="expertise"
                   value="{{ old('expertise', $user->expertise) }}"
                   placeholder="e.g. Plumbing, Electrical, Cleaning"
                   class="input input-bordered w-full @error('expertise') input-error @enderror">
            @error('expertise')
              <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
          </div>
          <div>
            <label for="experience_years" class="text-sm font-semibold text-base-content mb-1 block">Experience (years)</label>
            <input type="number" id="experience_years" name="experience_years" min="0" max="50"
                   value="{{ old('experience_years', $user->experience_years) }}"
                   class="input input-bordered w-full @error('experience_years') input-error @enderror">
            @error('experience_years')
              <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
          </div>
        </div>

      {{-- ═══ Customer-Only: Preferences ═══ --}}
      @else
        <div class="divider text-xs text-base-content/40 uppercase tracking-wider">Preferences</div>

        <div>
          <label for="alt_phone" class="text-sm font-semibold text-base-content mb-1 block">Alternate Phone</label>
          <input type="tel" id="alt_phone" name="alt_phone"
                 value="{{ old('alt_phone', $user->alt_phone) }}"
                 placeholder="+8801XXXXXXXXX"
                 class="input input-bordered w-full @error('alt_phone') input-error @enderror">
          <span class="text-xs text-base-content/40 mt-1 block">Providers can reach you at this number if the primary is unavailable.</span>
          @error('alt_phone')
            <span class="text-error text-xs mt-1">{{ $message }}</span>
          @enderror
        </div>
      @endif

      {{-- Buttons --}}
      <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('profile') }}" class="btn btn-ghost">Cancel</a>
      </div>
    </form>

  </div>
</section>

@push('scripts')
<script>
  function previewPhoto(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const container = document.getElementById('photo-preview-container');
        container.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover" alt="Preview" />';
      };
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>
@endpush

@endsection

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
          <label class="text-sm font-semibold text-base-content mb-1 block">Preferred Time Slots</label>
          @php
            $selectedSlots = old('preferred_time_slots', $user->preferred_time_slots ?? []);
            $timeSlotOptions = [
              'morning' => 'Morning',
              'afternoon' => 'Afternoon',
              'evening' => 'Evening',
              'weekend' => 'Weekend',
            ];
          @endphp
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            @foreach($timeSlotOptions as $value => $label)
              <label class="cursor-pointer rounded-xl border border-base-300 p-3 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                <input type="checkbox" name="preferred_time_slots[]" value="{{ $value }}" class="checkbox checkbox-primary checkbox-sm" {{ in_array($value, $selectedSlots, true) ? 'checked' : '' }}>
                <span class="ml-2 text-sm text-base-content">{{ $label }}</span>
              </label>
            @endforeach
          </div>
          @error('preferred_time_slots')
            <span class="text-error text-xs mt-1">{{ $message }}</span>
          @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="provider_gender_preference" class="text-sm font-semibold text-base-content mb-1 block">Provider Gender Preference</label>
            <select id="provider_gender_preference" name="provider_gender_preference" class="select select-bordered w-full @error('provider_gender_preference') select-error @enderror">
              <option value="">Any</option>
              <option value="male" {{ old('provider_gender_preference', $user->provider_gender_preference) === 'male' ? 'selected' : '' }}>Male</option>
              <option value="female" {{ old('provider_gender_preference', $user->provider_gender_preference) === 'female' ? 'selected' : '' }}>Female</option>
            </select>
            @error('provider_gender_preference')
              <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
          </div>
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
        </div>

        <div class="divider text-xs text-base-content/40 uppercase tracking-wider">Rewards</div>

        <div class="rounded-2xl bg-base-200/50 p-4 space-y-3">
          <div class="flex items-center justify-between gap-4">
            <div>
              <p class="text-sm font-semibold text-base-content">Available Points</p>
              <p class="text-xs text-base-content/50">Redeem points for wallet credit at 10 points = 1 credit unit.</p>
            </div>
            <div class="text-right">
              <p class="text-2xl font-black text-base-content">{{ $user->loyalty_points ?? 0 }}</p>
              <p class="text-xs text-base-content/50">points</p>
            </div>
          </div>

          <form method="POST" action="{{ route('profile.rewards.redeem') }}" class="grid gap-3 sm:grid-cols-[1fr_auto] items-end">
            @csrf
            <div>
              <label for="reward_points" class="text-sm font-semibold text-base-content mb-1 block">Redeem Points</label>
              <input type="number" id="reward_points" name="points" min="10" step="10" max="{{ $user->loyalty_points ?? 0 }}" class="input input-bordered w-full" placeholder="Enter points to redeem">
            </div>
            <button type="submit" class="btn btn-outline btn-sm">Redeem</button>
          </form>
        </div>

        <div class="divider text-xs text-base-content/40 uppercase tracking-wider">Saved Addresses</div>

        <div class="rounded-2xl border border-base-200 bg-base-100 p-5 space-y-4">
          <form method="POST" action="{{ route('profile.addresses.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="address_label" class="text-sm font-semibold text-base-content mb-1 block">Label</label>
                <input type="text" id="address_label" name="label" value="{{ old('label') }}" placeholder="Home, Office, etc." class="input input-bordered w-full">
              </div>
              <div>
                <label class="text-sm font-semibold text-base-content mb-1 block">Default</label>
                <label class="inline-flex items-center gap-2 rounded-xl border border-base-300 px-3 py-2">
                  <input type="checkbox" name="is_default" value="1" class="checkbox checkbox-primary checkbox-sm" {{ old('is_default') ? 'checked' : '' }}>
                  <span class="text-sm">Make this the default address</span>
                </label>
              </div>
            </div>

            <div>
              <label for="address_line1" class="text-sm font-semibold text-base-content mb-1 block">Address Line 1</label>
              <input type="text" id="address_line1" name="line1" value="{{ old('line1') }}" class="input input-bordered w-full" placeholder="Street, house, building">
            </div>

            <div>
              <label for="address_line2" class="text-sm font-semibold text-base-content mb-1 block">Address Line 2</label>
              <input type="text" id="address_line2" name="line2" value="{{ old('line2') }}" class="input input-bordered w-full" placeholder="Apartment, floor, landmark">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label for="address_city" class="text-sm font-semibold text-base-content mb-1 block">City</label>
                <input type="text" id="address_city" name="city" value="{{ old('city') }}" class="input input-bordered w-full">
              </div>
              <div>
                <label for="address_area" class="text-sm font-semibold text-base-content mb-1 block">Area</label>
                <input type="text" id="address_area" name="area" value="{{ old('area') }}" class="input input-bordered w-full">
              </div>
              <div>
                <label for="address_postal_code" class="text-sm font-semibold text-base-content mb-1 block">Postal Code</label>
                <input type="text" id="address_postal_code" name="postal_code" value="{{ old('postal_code') }}" class="input input-bordered w-full">
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Save Address</button>
          </form>

          <div class="space-y-3">
            @forelse($addresses as $address)
              <div class="rounded-xl border border-base-200 p-4">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <p class="font-semibold text-base-content">{{ $address->label }}</p>
                      @if($address->is_default)
                        <span class="badge badge-primary badge-sm">Default</span>
                      @endif
                    </div>
                    <p class="text-sm text-base-content/70 mt-1">{{ $address->line1 }}</p>
                    @if($address->line2)
                      <p class="text-sm text-base-content/70">{{ $address->line2 }}</p>
                    @endif
                    <p class="text-xs text-base-content/50 mt-1">{{ collect([$address->area, $address->city, $address->postal_code])->filter()->implode(', ') }}</p>
                  </div>
                  <div class="flex flex-wrap gap-2 justify-end">
                    @unless($address->is_default)
                      <form method="POST" action="{{ route('profile.addresses.default', $address) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-xs">Set Default</button>
                      </form>
                    @endunless
                    <form method="POST" action="{{ route('profile.addresses.destroy', $address) }}" onsubmit="return confirm('Delete this address?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-error btn-outline btn-xs">Delete</button>
                    </form>
                  </div>
                </div>
              </div>
            @empty
              <p class="text-sm text-base-content/60">No saved addresses yet.</p>
            @endforelse
          </div>
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

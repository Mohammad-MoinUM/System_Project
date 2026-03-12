@extends('layouts.form')

@section('title', 'Complete Your Provider Profile')
@section('content')
<h2 class="text-2xl font-bold text-base-content">Provider Profile</h2>
<p class="text-base-content/60 mt-1 mb-8">Tell us about yourself and the services you offer.</p>

<form id="onboarding-form" method="POST" action="{{ route('onboarding.provider.store') }}" enctype="multipart/form-data">
    @csrf

    {{-- Profile Photo --}}
    <div class="flex flex-col items-center mb-8">
        <span class="text-sm font-semibold text-base-content mb-3">Profile Photo</span>
        <label for="photo" class="cursor-pointer group">
            <div class="w-24 h-24 rounded-full border-2 border-dashed border-base-300 group-hover:border-base-content/40 flex flex-col items-center justify-center text-base-content/40 group-hover:text-base-content/60 transition-colors overflow-hidden"
                 id="photo-preview-container">
                @if(auth()->user()->photo)
                    <img src="{{ asset('storage/' . auth()->user()->photo) }}" class="w-full h-full object-cover" alt="Profile">
                @else
                    <x-heroicon-o-camera class="w-8 h-8" />
                    <span class="text-xs mt-1">Upload</span>
                @endif
            </div>
            <input type="file" id="photo" name="photo" accept="image/*" class="hidden" onchange="previewPhoto(this)">
        </label>
        @error('photo')
            <span class="text-error text-xs mt-1">{{ $message }}</span>
        @enderror
    </div>

    {{-- ═══ Personal Info ═══ --}}
    <div class="divider text-xs text-base-content/40 uppercase tracking-wider mb-6">Personal Information</div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="first_name" class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
                <x-heroicon-o-user class="w-4 h-4 text-base-content/50" />
                First Name <span class="text-error">*</span>
            </label>
            <input type="text" id="first_name" name="first_name"
                   value="{{ old('first_name', auth()->user()->first_name) }}"
                   placeholder="Enter your first name"
                   class="input input-bordered w-full @error('first_name') input-error @enderror"
                   required>
            @error('first_name')
                <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="last_name" class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
                <x-heroicon-o-user class="w-4 h-4 text-base-content/50" />
                Last Name <span class="text-error">*</span>
            </label>
            <input type="text" id="last_name" name="last_name"
                   value="{{ old('last_name', auth()->user()->last_name) }}"
                   placeholder="Enter your last name"
                   class="input input-bordered w-full @error('last_name') input-error @enderror"
                   required>
            @error('last_name')
                <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="phone" class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
                <x-heroicon-o-phone class="w-4 h-4 text-base-content/50" />
                Phone Number <span class="text-error">*</span>
            </label>
            <input type="tel" id="phone" name="phone"
                   value="{{ old('phone', auth()->user()->phone) }}"
                   placeholder="+8801XXXXXXXXX"
                   class="input input-bordered w-full @error('phone') input-error @enderror"
                   required>
            @error('phone')
                <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="nid_number" class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
                <x-heroicon-o-identification class="w-4 h-4 text-base-content/50" />
                NID Number <span class="text-error">*</span>
            </label>
            <input type="text" id="nid_number" name="nid_number"
                   value="{{ old('nid_number', auth()->user()->nid_number) }}"
                   placeholder="e.g. 1234567890"
                   class="input input-bordered w-full @error('nid_number') input-error @enderror"
                   required>
            @error('nid_number')
                <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Address --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="city" class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
                <x-heroicon-o-map-pin class="w-4 h-4 text-base-content/50" />
                City <span class="text-error">*</span>
            </label>
            <select id="city" name="city"
                    onchange="populateAreas(this.value)"
                    class="select select-bordered w-full @error('city') select-error @enderror"
                    required>
                <option value="" disabled {{ old('city', auth()->user()->city) ? '' : 'selected' }}>Select a city</option>
            </select>
            @error('city')
                <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="area" class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
                <x-heroicon-o-map-pin class="w-4 h-4 text-base-content/50" />
                Area <span class="text-error">*</span>
            </label>
            <select id="area" name="area"
                    class="select select-bordered w-full @error('area') select-error @enderror"
                    required disabled>
                <option value="" disabled selected>Select a city first</option>
            </select>
            @error('area')
                <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- ═══ Academic Background ═══ --}}
    <div class="divider text-xs text-base-content/40 uppercase tracking-wider mb-6">Academic Background</div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="education" class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
                <x-heroicon-o-academic-cap class="w-4 h-4 text-base-content/50" />
                Highest Education <span class="text-error">*</span>
            </label>
            <select id="education" name="education"
                    class="select select-bordered w-full @error('education') select-error @enderror"
                    required>
                <option value="" disabled {{ old('education', auth()->user()->education) ? '' : 'selected' }}>Select education level</option>
                @foreach (['SSC', 'HSC', 'Diploma', 'Bachelor\'s', 'Master\'s', 'PhD', 'Other'] as $level)
                    <option value="{{ $level }}" {{ old('education', auth()->user()->education) === $level ? 'selected' : '' }}>{{ $level }}</option>
                @endforeach
            </select>
            @error('education')
                <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="institution" class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
                <x-heroicon-o-building-library class="w-4 h-4 text-base-content/50" />
                Institution
                <span class="text-base-content/40 text-xs font-normal">(optional)</span>
            </label>
            <input type="text" id="institution" name="institution"
                   value="{{ old('institution', auth()->user()->institution) }}"
                   placeholder="e.g. University of Dhaka"
                   class="input input-bordered w-full @error('institution') input-error @enderror">
            @error('institution')
                <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Certifications --}}
    <div class="mb-6">
        <label class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
            <x-heroicon-o-document-check class="w-4 h-4 text-base-content/50" />
            Certifications / Licenses
            <span class="text-base-content/40 text-xs font-normal">(optional)</span>
        </label>
        <div id="certifications-container" class="space-y-2">
            @php $certs = old('certifications', auth()->user()->certifications ?? []); @endphp
            @forelse ($certs as $i => $cert)
                <div class="flex gap-2 certification-row">
                    <input type="text" name="certifications[]" value="{{ $cert }}"
                           placeholder="e.g. Licensed Electrician"
                           class="input input-bordered w-full input-sm">
                    <button type="button" onclick="this.closest('.certification-row').remove()" class="btn btn-ghost btn-sm btn-square text-error">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                </div>
            @empty
                <div class="flex gap-2 certification-row">
                    <input type="text" name="certifications[]"
                           placeholder="e.g. Licensed Electrician"
                           class="input input-bordered w-full input-sm">
                    <button type="button" onclick="this.closest('.certification-row').remove()" class="btn btn-ghost btn-sm btn-square text-error">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                </div>
            @endforelse
        </div>
        <button type="button" onclick="addCertification()" class="btn btn-ghost btn-xs mt-2 gap-1">
            <x-heroicon-o-plus class="w-3 h-3" /> Add another
        </button>
    </div>

    {{-- ═══ Professional Info ═══ --}}
    <div class="divider text-xs text-base-content/40 uppercase tracking-wider mb-6">Professional Details</div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="expertise" class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
                <x-heroicon-o-bolt class="w-4 h-4 text-base-content/50" />
                Area of Expertise <span class="text-error">*</span>
            </label>
            <input type="text" id="expertise" name="expertise"
                   value="{{ old('expertise', auth()->user()->expertise) }}"
                   placeholder="e.g. Plumbing, Electrical Wiring"
                   class="input input-bordered w-full @error('expertise') input-error @enderror"
                   required>
            @error('expertise')
                <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="experience_years" class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
                <x-heroicon-o-clock class="w-4 h-4 text-base-content/50" />
                Years of Experience <span class="text-error">*</span>
            </label>
            <select id="experience_years" name="experience_years"
                    class="select select-bordered w-full @error('experience_years') select-error @enderror"
                    required>
                <option value="" disabled {{ old('experience_years', auth()->user()->experience_years) === null ? 'selected' : '' }}>Select</option>
                @foreach (['0' => 'Less than 1 year', '1' => '1 year', '2' => '2 years', '3' => '3 years', '4' => '4 years', '5' => '5+ years', '10' => '10+ years'] as $val => $label)
                    <option value="{{ $val }}" {{ (string) old('experience_years', auth()->user()->experience_years) === (string) $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('experience_years')
                <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Services Offered --}}
    <div class="mb-6">
        <label class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
            <x-heroicon-o-wrench-screwdriver class="w-4 h-4 text-base-content/50" />
            Services You Can Provide <span class="text-error">*</span>
        </label>
        <p class="text-xs text-base-content/50 mb-3">Select all that apply.</p>
        @php
            $serviceOptions = [
                'Plumbing', 'Electrical', 'Carpentry', 'Painting', 'Cleaning',
                'AC Repair', 'Appliance Repair', 'Landscaping', 'Pest Control',
                'Moving & Shifting', 'Home Renovation', 'Interior Design',
                'Tutoring', 'IT Support', 'Photography', 'Catering', 'Tailoring',
                'Beauty & Grooming', 'Fitness Training', 'Other',
            ];
            $selectedServices = old('services_offered', auth()->user()->services_offered ?? []);
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach ($serviceOptions as $service)
                <label class="flex items-center gap-2 cursor-pointer rounded-lg border border-base-300 px-3 py-2 hover:bg-base-200 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                    <input type="checkbox" name="services_offered[]" value="{{ $service }}"
                           class="checkbox checkbox-sm checkbox-primary"
                           {{ in_array($service, $selectedServices) ? 'checked' : '' }}>
                    <span class="text-sm">{{ $service }}</span>
                </label>
            @endforeach
        </div>
        @error('services_offered')
            <span class="text-error text-xs mt-1">{{ $message }}</span>
        @enderror
    </div>

    {{-- Bio --}}
    <div class="mb-6">
        <label for="bio" class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
            <x-heroicon-o-document-text class="w-4 h-4 text-base-content/50" />
            About You
            <span class="text-base-content/40 text-xs font-normal">(optional)</span>
        </label>
        <textarea id="bio" name="bio" rows="4"
                  placeholder="Briefly describe your background, skills, and what makes you a great service provider..."
                  class="textarea textarea-bordered w-full @error('bio') textarea-error @enderror">{{ old('bio', auth()->user()->bio) }}</textarea>
        @error('bio')
            <span class="text-error text-xs mt-1">{{ $message }}</span>
        @enderror
    </div>

</form>
@endsection

@section('buttons')
<div class="flex items-center justify-between mt-8">
    <a href="{{ url()->previous() }}" class="btn btn-ghost gap-1 text-base-content/70 hover:text-base-content">
        <x-heroicon-o-arrow-left class="w-4 h-4" />
        Back
    </a>
    <button type="submit" form="onboarding-form" class="btn btn-neutral gap-2">
        Save & Continue
        <x-heroicon-o-arrow-right class="w-4 h-4" />
    </button>
</div>
@endsection

{{-- Photo Preview Script --}}
@push('scripts')
<script>
    // Bangladesh City → Areas map
    const cityAreas = {
        "Dhaka": [
            "Adabor", "Badda", "Banani", "Baridhara", "Bashabo", "Bashundhara",
            "Demra", "Dhanmondi", "Gulshan", "Hazaribagh", "Jatrabari",
            "Kafrul", "Kalabagan", "Khilgaon", "Khilkhet", "Lalbagh",
            "Mirpur", "Mohakhali", "Mohammadpur", "Motijheel", "Mugda",
            "New Market", "Pallabi", "Panthapath", "Rampura", "Sabujbagh",
            "Shyamoli", "Tejgaon", "Uttara", "Wari"
        ],
        "Chittagong": [
            "Agrabad", "Akbar Shah", "Bakalia", "Bayazid", "Chandgaon",
            "Chawk Bazaar", "Double Mooring", "Halishahar", "Khulshi",
            "Kotwali", "Panchlaish", "Pahartali", "Patenga", "Sadarghat"
        ],
        "Sylhet": [
            "Airportroad", "Ambarkhana", "Bandarbazar", "Jigatala",
            "Kadamtali", "Kumarpara", "Laldighirpar", "Mirer Maidan",
            "Modina Market", "Shahjalal Upashahar", "Shibganj", "Tilagarh", "Zindabazar"
        ],
        "Rajshahi": [
            "Boalia", "Matihar", "Rajpara", "Shah Makhdum", "Shaheb Bazaar",
            "Upashahar", "Kazla", "Padma Residential", "Sopura"
        ],
        "Khulna": [
            "Daulatpur", "Khan Jahan Ali", "Khalishpur", "Khulna Sadar",
            "Labanchara", "Sonadanga", "Boyra", "Nirala", "Gollamari"
        ],
        "Barisal": [
            "Airport Road", "Bagura Road", "Band Road", "Chawkbazar",
            "Chor Monai", "Kalibari Road", "Kaunia", "Natullabad", "Rupatali", "Sadar"
        ],
        "Comilla": [
            "Candirpar", "Chowddagram", "Kotbari", "Laksam", "Muradnagar",
            "Nangalkot", "Sadar South", "Sadar North", "Tomsom Bridge"
        ],
        "Narayanganj": [
            "AK Khan", "Araihazar", "Bandar", "Fatullah", "Rupganj",
            "Siddhirganj", "Sonargaon", "Sadar"
        ],
        "Gazipur": [
            "Boardbazar", "Chandana", "Joydebpur", "Kaliakoir", "Kapasia",
            "Pubail", "Sreepur", "Tongi", "Kashimpur"
        ],
        "Mymensingh": [
            "Bhaluka", "Fulbaria", "Gaffargaon", "Gauripur", "Haluaghat",
            "Muktagacha", "Nandail", "Phulpur", "Sadar", "Trishal"
        ]
    };

    // Populate city dropdown on page load
    document.addEventListener('DOMContentLoaded', function () {
        const citySelect = document.getElementById('city');
        const oldCity = "{{ old('city', auth()->user()->city) }}";
        const oldArea = "{{ old('area', auth()->user()->area) }}";

        Object.keys(cityAreas).sort().forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            if (city === oldCity) option.selected = true;
            citySelect.appendChild(option);
        });

        // Restore old area if validation failed
        if (oldCity) {
            populateAreas(oldCity, oldArea);
        }
    });

    function populateAreas(selectedCity, preselectArea = null) {
        const areaSelect = document.getElementById('area');
        areaSelect.innerHTML = '';
        areaSelect.disabled = false;

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.disabled = true;
        placeholder.textContent = 'Select an area';
        if (!preselectArea) placeholder.selected = true;
        areaSelect.appendChild(placeholder);

        const areas = cityAreas[selectedCity] || [];
        areas.forEach(area => {
            const option = document.createElement('option');
            option.value = area;
            option.textContent = area;
            if (area === preselectArea) option.selected = true;
            areaSelect.appendChild(option);
        });
    }

    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const container = document.getElementById('photo-preview-container');
                container.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover" alt="Preview">`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function addCertification() {
        const container = document.getElementById('certifications-container');
        const row = document.createElement('div');
        row.className = 'flex gap-2 certification-row';
        row.innerHTML = `
            <input type="text" name="certifications[]"
                   placeholder="e.g. Licensed Electrician"
                   class="input input-bordered w-full input-sm">
            <button type="button" onclick="this.closest('.certification-row').remove()" class="btn btn-ghost btn-sm btn-square text-error">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>`;
        container.appendChild(row);
    }
</script>
@endpush
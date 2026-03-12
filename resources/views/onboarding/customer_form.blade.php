@extends('layouts.form')

@section('title', 'Complete Your Profile')
@section('content')
<h2 class="text-2xl font-bold text-base-content">Basic Information</h2>
<p class="text-base-content/60 mt-1 mb-8">Tell us a bit about yourself.</p>

<form id="onboarding-form" method="POST" action="{{ route('onboarding.customer.store') }}" enctype="multipart/form-data">
    @csrf

    {{-- Profile Photo --}}
    <div class="flex flex-col items-center mb-8">
        <span class="text-sm font-semibold text-base-content mb-3">Profile Photo</span>
        <label for="photo" class="cursor-pointer group">
            <div class="w-24 h-24 rounded-full border-2 border-dashed border-base-300 group-hover:border-base-content/40 flex flex-col items-center justify-center text-base-content/40 group-hover:text-base-content/60 transition-colors overflow-hidden"
                 id="photo-preview-container">
                <x-heroicon-o-camera class="w-8 h-8" />
                <span class="text-xs mt-1">Upload</span>
            </div>
            <input type="file" id="photo" name="photo" accept="image/*" class="hidden" onchange="previewPhoto(this)">
        </label>
        @error('photo')
            <span class="text-error text-xs mt-1">{{ $message }}</span>
        @enderror
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="first_name" class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
                <x-heroicon-o-user class="w-4 h-4 text-base-content/50" />
                First Name <span class="text-error">*</span>
            </label>
            <input type="text" id="first_name" name="first_name"
                   value="{{ old('first_name', auth()->user()->first_name) }}"
                   placeholder="Enter your first name"
                   autocomplete="given-name"
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
                   autocomplete="family-name"
                   class="input input-bordered w-full @error('last_name') input-error @enderror"
                   required>
            @error('last_name')
                <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Phone Numbers --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="phone" class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
                <x-heroicon-o-phone class="w-4 h-4 text-base-content/50" />
                Phone Number <span class="text-error">*</span>
            </label>
            <input type="tel" id="phone" name="phone"
                   value="{{ old('phone', auth()->user()->phone) }}"
                   placeholder="+8801XXXXXXXXX"
                   autocomplete="tel"
                   class="input input-bordered w-full @error('phone') input-error @enderror"
                   required>
            @error('phone')
                <span class="text-error text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="alt_phone" class="flex items-center gap-2 text-sm font-semibold text-base-content mb-2">
                <x-heroicon-o-phone class="w-4 h-4 text-base-content/50" />
                Alt Phone Number
                <span class="text-base-content/40 text-xs font-normal">(optional)</span>
            </label>
            <input type="tel" id="alt_phone" name="alt_phone"
                   value="{{ old('alt_phone', auth()->user()->alt_phone) }}"
                   placeholder="+8801XXXXXXXXX"
                   autocomplete="tel"
                   class="input input-bordered w-full @error('alt_phone') input-error @enderror">
            @error('alt_phone')
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
                <option value="" disabled {{ old('city') ? '' : 'selected' }}>Select a city</option>
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
</script>
@endpush
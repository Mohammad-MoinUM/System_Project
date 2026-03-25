@extends('layouts.app')

@section('content')

@php
    $currencyOptions = config('currencies.options', []);
    $currency = session('currency', config('currencies.default', 'BDT'));
    $currencyMeta = $currencyOptions[$currency] ?? ['symbol' => $currency, 'rate' => 1];
    $currencySymbol = $currencyMeta['symbol'] ?? $currency;
    $currencyRate = $currencyMeta['rate'] ?? 1;
@endphp

<section class="bg-base-200">
  <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ url()->previous() }}" class="inline-flex items-center gap-1.5 text-sm text-base-content/60 hover:text-base-content mb-6 transition-colors">
      <x-heroicon-o-arrow-left class="w-4 h-4" />
      Back
    </a>

    <h1 class="text-3xl font-bold text-base-content">Book a Service</h1>
    <p class="mt-2 text-base-content/60">Fill in the details below to place your booking.</p>

    @if($errors->any())
      <div class="alert alert-error mt-4">
        <ul class="list-disc list-inside">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Service Info Card --}}
    <div class="mt-8 rounded-2xl border border-base-200 bg-base-100 p-6">
      <div class="flex items-start gap-4">
        @if($service->provider && $service->provider->photo)
          <img src="{{ asset('storage/' . $service->provider->photo) }}" alt="" class="w-14 h-14 rounded-full object-cover" />
        @else
          <div class="w-14 h-14 rounded-full bg-base-300 flex items-center justify-center">
            <x-heroicon-o-user class="w-7 h-7 text-base-content/20" />
          </div>
        @endif
        <div>
          <h3 class="text-lg font-bold text-base-content">{{ $service->name }}</h3>
          <p class="text-sm text-base-content/60">by {{ $service->provider->first_name ?? '' }} {{ $service->provider->last_name ?? '' }}</p>
          @if($service->provider->city || $service->provider->area)
            <p class="text-xs text-base-content/40 mt-1 flex items-center gap-1">
              <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
              {{ collect([$service->provider->area, $service->provider->city])->filter()->implode(', ') }}
            </p>
          @endif
        </div>
        <div class="ml-auto text-right">
          <p class="text-2xl font-bold text-primary">{{ $currencySymbol }} {{ number_format(($service->price ?? 0) * $currencyRate, 2) }}</p>
          <p class="text-xs text-base-content/40">{{ $service->category }}</p>
        </div>
      </div>
      @if($service->description)
        <p class="mt-4 text-sm text-base-content/60 border-t border-base-200 pt-4">{{ $service->description }}</p>
      @endif
    </div>

    {{-- Booking Form --}}
    <form method="POST" action="{{ route('booking.store') }}" class="mt-8 space-y-6">
      @csrf
      <input type="hidden" name="service_id" value="{{ $service->id }}" />

      <!-- Smart Slot Booking Section -->
      <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 p-6">
        <h3 class="text-lg font-bold text-base-content mb-1">📅 Choose Your Time Slot</h3>
        <p class="text-sm text-base-content/60 mb-4">Select a date and available time from the provider's schedule</p>

        <!-- Date Selection -->
        <div>
          <label class="label"><span class="label-text font-semibold">Select Date</span></label>
          <select name="booking_date" id="booking_date" class="select select-bordered w-full" onchange="loadAvailableSlots()">
            <option value="">-- Choose a date --</option>
            @foreach($availableDates as $dateOption)
              <option value="{{ $dateOption['date'] }}">
                {{ $dateOption['display'] }} ({{ $dateOption['day'] }})
              </option>
            @endforeach
          </select>
          @error('booking_date') <span class="text-error text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Slot Selection -->
        <div class="mt-4">
          <label class="label"><span class="label-text font-semibold">Select Time Slot (60 min)</span></label>
          <div id="slots-container" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            <div class="text-center p-4 text-base-content/50">
              <p class="text-sm">Select a date first to see available slots</p>
            </div>
          </div>
          @error('time_from') <span class="text-error text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Hidden fields for slot timing -->
        <input type="hidden" name="time_from" id="time_from">
        <input type="hidden" name="time_to" id="time_to">
        <input type="hidden" name="slot_duration_minutes" value="60">
      </div>

      <!-- Alternative: Legacy Date-Time Option (hidden by default) -->
      <details class="collapse bg-base-200" open>
        <summary class="collapse-title font-semibold text-base cursor-pointer">
          Or use legacy date-time selection
        </summary>
        <div class="collapse-content">
          <div>
            <label class="label"><span class="label-text font-semibold">Preferred Date & Time</span></label>
            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                   min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                   class="input input-bordered w-full" />
            @error('scheduled_at') <span class="text-error text-sm">{{ $message }}</span> @enderror
          </div>
        </div>
      </details>

      <div>
        <label class="label"><span class="label-text font-semibold">Notes (optional)</span></label>
        <textarea name="notes" rows="3" class="textarea textarea-bordered w-full"
                  placeholder="Any special instructions or details...">{{ old('notes') }}</textarea>
        @error('notes') <span class="text-error text-sm">{{ $message }}</span> @enderror
      </div>

      <div class="rounded-xl bg-base-200 p-4">
        <div class="flex justify-between text-sm">
          <span class="text-base-content/60">Service Price</span>
          <span class="font-semibold text-base-content">{{ $currencySymbol }} {{ number_format(($service->price ?? 0) * $currencyRate, 2) }}</span>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-lg w-full">Confirm Booking</button>
    </form>

    <script>
      function loadAvailableSlots() {
        const bookingDate = document.getElementById('booking_date').value;
        const slotsContainer = document.getElementById('slots-container');
        
        if (!bookingDate) {
          slotsContainer.innerHTML = '<div class="col-span-full text-center p-4 text-base-content/50"><p class="text-sm">Select a date first</p></div>';
          return;
        }

        // Show loading state
        slotsContainer.innerHTML = '<div class="col-span-full text-center p-4"><span class="loading loading-spinner loading-md"></span></div>';

        // Fetch available slots via AJAX
        fetch('{{ route("provider.availability.get-slots") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            provider_id: {{ $service->provider_id }},
            date: bookingDate,
            slot_duration: 60
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success && data.slots.length > 0) {
            slotsContainer.innerHTML = data.slots.map((slot, index) => `
              <label class="btn btn-outline btn-sm btn-block" data-slot-index="${index}">
                <input type="radio" name="slot" value="${index}" class="hidden" 
                       onchange="selectSlot('${slot.time_from}', '${slot.time_to}')">
                <span>${slot.display}</span>
              </label>
            `).join('');
          } else {
            slotsContainer.innerHTML = '<div class="col-span-full text-center p-4 text-error"><p class="text-sm">No available slots for this date</p></div>';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          slotsContainer.innerHTML = '<div class="col-span-full text-center p-4 text-error"><p class="text-sm">Error loading slots</p></div>';
        });
      }

      function selectSlot(timeFrom, timeTo) {
        document.getElementById('time_from').value = timeFrom;
        document.getElementById('time_to').value = timeTo;
      }
    </script>
  </div>
</section>

@endsection

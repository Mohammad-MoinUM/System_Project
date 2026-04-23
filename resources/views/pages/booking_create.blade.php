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
          <div class="mt-2 flex flex-wrap gap-2">
            @if(($service->provider->verification_status ?? null) === 'approved')
              <span class="badge badge-success badge-sm">Verified Provider</span>
            @endif
            @if(($service->provider->skill_verification_status ?? null) === 'verified')
              <span class="badge badge-info badge-sm">Skills Checked</span>
            @endif
            @if($service->is_insured)
              <span class="badge badge-info badge-sm">Insured Service</span>
            @endif
            @if($service->guarantee_enabled)
              <span class="badge badge-success badge-sm">Service Guarantee</span>
            @endif
          </div>
          @if($service->provider->city || $service->provider->area)
            <p class="text-xs text-base-content/40 mt-1 flex items-center gap-1">
              <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
              {{ collect([$service->provider->area, $service->provider->city])->filter()->implode(', ') }}
            </p>
          @endif
        </div>
        <div class="ml-auto text-right">
          @if(!empty($service->flash_deal_price) && $service->flash_deal_ends_at && now()->lt($service->flash_deal_ends_at))
            <p class="text-sm text-base-content/50 line-through">{{ $currencySymbol }} {{ number_format(($service->price ?? 0) * $currencyRate, 2) }}</p>
            <p class="text-2xl font-bold text-primary">{{ $currencySymbol }} {{ number_format(($service->flash_deal_price ?? 0) * $currencyRate, 2) }}</p>
            <p class="text-xs text-warning">Flash deal ends {{ $service->flash_deal_ends_at->diffForHumans() }}</p>
          @else
            <p class="text-2xl font-bold text-primary">{{ $currencySymbol }} {{ number_format(($service->price ?? 0) * $currencyRate, 2) }}</p>
          @endif
          <p class="text-xs text-base-content/40">{{ $service->category }}</p>
        </div>
      </div>
      <div class="mt-3 flex justify-end">
        <form method="POST" action="{{ route('saved-services.toggle', $service) }}">
          @csrf
          <button type="submit" class="btn btn-outline btn-xs">Save to Wishlist</button>
        </form>
      </div>
      @if($service->description)
        <p class="mt-4 text-sm text-base-content/60 border-t border-base-200 pt-4">{{ $service->description }}</p>
      @endif
    </div>

    @if(!empty($customer?->preferred_time_slots) || !empty($customer?->provider_gender_preference))
      <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
        <h3 class="font-bold">Your saved preferences</h3>
        <div class="mt-3 flex flex-wrap gap-2">
          @foreach(($customer->preferred_time_slots ?? []) as $slot)
            <span class="badge badge-outline badge-warning">{{ ucfirst($slot) }}</span>
          @endforeach
          @if($customer?->provider_gender_preference)
            <span class="badge badge-outline badge-warning">{{ ucfirst($customer->provider_gender_preference) }} provider</span>
          @endif
        </div>
      </div>
    @endif

    {{-- Booking Form --}}
    <form method="POST" action="{{ route('booking.store') }}" class="mt-8 space-y-6" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="service_id" value="{{ $service->id }}" />

      <!-- Booking Mode -->
      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h3 class="text-lg font-bold text-base-content mb-3">How would you like to book?</h3>
        <div class="grid gap-3 sm:grid-cols-2">
          <label class="cursor-pointer rounded-xl border border-base-300 p-4 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
            <input type="radio" name="booking_mode" value="instant" class="radio radio-primary" checked onchange="toggleBookingMode()" />
            <div class="mt-3">
              <p class="font-semibold text-base-content">Book now</p>
              <p class="text-sm text-base-content/60">Send an immediate request for the next available visit.</p>
            </div>
          </label>
          <label class="cursor-pointer rounded-xl border border-base-300 p-4 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
            <input type="radio" name="booking_mode" value="scheduled" class="radio radio-primary" onchange="toggleBookingMode()" />
            <div class="mt-3">
              <p class="font-semibold text-base-content">Pick a future slot</p>
              <p class="text-sm text-base-content/60">Choose a specific date and time from the provider schedule.</p>
            </div>
          </label>
        </div>
      </div>

      <!-- Smart Slot Booking Section -->
      <div id="scheduled-booking-section" class="rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 p-6 hidden">
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

      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h3 class="text-lg font-bold text-base-content mb-3">Add-ons and schedule</h3>

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="label"><span class="label-text font-semibold">Recurring service</span></label>
            <select name="recurrence_type" class="select select-bordered w-full" onchange="toggleRecurringFields()">
              <option value="none">One-time booking</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
            </select>
          </div>
          <div>
            <label class="label"><span class="label-text font-semibold">Repeat every</span></label>
            <select name="recurrence_interval" class="select select-bordered w-full">
              <option value="1">Every 1 interval</option>
              <option value="2">Every 2 intervals</option>
              <option value="3">Every 3 intervals</option>
            </select>
          </div>
        </div>

        <div id="recurrence-end-date" class="mt-4 hidden">
          <label class="label"><span class="label-text font-semibold">Repeat until</span></label>
          <input type="date" name="recurrence_end_date" class="input input-bordered w-full" min="{{ now()->toDateString() }}" />
        </div>
      </div>

      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h3 class="text-lg font-bold text-base-content mb-3">Service address</h3>

        @if($customerAddresses->isNotEmpty())
          <div class="grid gap-3 sm:grid-cols-2 mb-4">
            <label class="cursor-pointer rounded-xl border border-base-300 p-4 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
              <input type="radio" name="service_address_source" value="saved" class="radio radio-primary" checked onchange="toggleAddressSource()" />
              <div class="mt-3">
                <p class="font-semibold text-base-content">Use saved address</p>
                <p class="text-sm text-base-content/60">Pick one of your saved locations.</p>
              </div>
            </label>
            <label class="cursor-pointer rounded-xl border border-base-300 p-4 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
              <input type="radio" name="service_address_source" value="manual" class="radio radio-primary" onchange="toggleAddressSource()" />
              <div class="mt-3">
                <p class="font-semibold text-base-content">Enter a new address</p>
                <p class="text-sm text-base-content/60">Use a one-time service location.</p>
              </div>
            </label>
          </div>

          <div id="saved-address-block" class="space-y-3">
            <label class="label"><span class="label-text font-semibold">Saved address</span></label>
            <select name="saved_address_id" id="saved_address_id" class="select select-bordered w-full" onchange="fillSavedAddress()">
              @foreach($customerAddresses as $address)
                <option
                  value="{{ $address->id }}"
                  data-label="{{ $address->label }}"
                  data-line1="{{ $address->line1 }}"
                  data-line2="{{ $address->line2 }}"
                  data-city="{{ $address->city }}"
                  data-area="{{ $address->area }}"
                  data-postal-code="{{ $address->postal_code }}"
                  {{ $address->is_default ? 'selected' : '' }}
                >
                  {{ $address->label }} - {{ collect([$address->area, $address->city])->filter()->implode(', ') }}
                </option>
              @endforeach
            </select>
          </div>
        @else
          <input type="hidden" name="service_address_source" value="manual" />
        @endif

        <div id="manual-address-block" class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label class="label"><span class="label-text font-semibold">Address label</span></label>
            <input type="text" name="service_address_label" class="input input-bordered w-full" placeholder="Home, Office, etc.">
          </div>
          <div>
            <label class="label"><span class="label-text font-semibold">Postal code</span></label>
            <input type="text" name="service_postal_code" class="input input-bordered w-full" placeholder="Postal code">
          </div>
          <div class="sm:col-span-2">
            <label class="label"><span class="label-text font-semibold">Address line 1</span></label>
            <input type="text" name="service_address_line1" class="input input-bordered w-full" placeholder="Street, house, building">
          </div>
          <div class="sm:col-span-2">
            <label class="label"><span class="label-text font-semibold">Address line 2</span></label>
            <input type="text" name="service_address_line2" class="input input-bordered w-full" placeholder="Apartment, floor, landmark">
          </div>
          <div>
            <label class="label"><span class="label-text font-semibold">City</span></label>
            <input type="text" name="service_city" class="input input-bordered w-full" placeholder="City">
          </div>
          <div>
            <label class="label"><span class="label-text font-semibold">Area</span></label>
            <input type="text" name="service_area" class="input input-bordered w-full" placeholder="Area">
          </div>
        </div>
      </div>

      @if(isset($bundleServices) && $bundleServices->isNotEmpty())
        <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
          <h3 class="text-lg font-bold text-base-content mb-2">Add more services</h3>
          <p class="text-sm text-base-content/60 mb-4">Bundle related services into one order.</p>
          <div class="grid gap-3 sm:grid-cols-2">
            @foreach($bundleServices as $bundleService)
              <label class="flex items-start gap-3 rounded-xl border border-base-200 p-4 hover:border-primary transition-colors cursor-pointer">
                <input type="checkbox" name="extra_service_ids[]" value="{{ $bundleService->id }}" class="checkbox checkbox-primary mt-1" />
                <div>
                  <p class="font-semibold text-base-content">{{ $bundleService->name }}</p>
                  <p class="text-sm text-base-content/60">{{ $bundleService->category }} · {{ $currencySymbol }} {{ number_format(($bundleService->price ?? 0) * $currencyRate, 2) }}</p>
                </div>
              </label>
            @endforeach
          </div>
        </div>
      @endif

      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h3 class="text-lg font-bold text-base-content mb-3">Payment method</h3>
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="label"><span class="label-text font-semibold">Choose payment method</span></label>
            <select name="payment_method" class="select select-bordered w-full">
              <option value="bkash">bKash</option>
              <option value="nagad">Nagad</option>
              <option value="card">Card</option>
              <option value="cash">Cash on service</option>
              <option value="wallet">Wallet / Credits</option>
            </select>
          </div>
          <div>
            <label class="label"><span class="label-text font-semibold">Payment split</span></label>
            <select name="payment_split_type" id="payment_split_type" class="select select-bordered w-full" onchange="togglePaymentSplit()">
              <option value="full">Pay full now</option>
              <option value="partial">Pay partial upfront</option>
            </select>
          </div>
        </div>

        <div id="upfront-payment-wrap" class="mt-4 hidden">
          <label class="label"><span class="label-text font-semibold">Upfront percentage</span></label>
          <select name="upfront_percent" class="select select-bordered w-full">
            <option value="20">20%</option>
            <option value="30" selected>30%</option>
            <option value="50">50%</option>
            <option value="70">70%</option>
          </select>
          <p class="mt-2 text-sm text-base-content/60">The remaining amount will be paid after the service is completed.</p>
        </div>

        <div class="mt-4">
          <label class="label"><span class="label-text font-semibold">Promo code</span></label>
          <input type="text" name="promo_code" value="{{ old('promo_code') }}" class="input input-bordered w-full" placeholder="Enter coupon code">
          <p class="mt-1 text-xs text-base-content/50">Subscription discounts apply automatically if active.</p>
          @error('promo_code') <span class="text-error text-sm">{{ $message }}</span> @enderror
        </div>
      </div>

      <div class="rounded-2xl border border-base-200 bg-base-100 p-6">
        <h3 class="text-lg font-bold text-base-content mb-3">Upload photos or video</h3>
        <p class="text-sm text-base-content/60 mb-4">Help the provider understand the issue before arrival.</p>
        <input type="file" name="attachments[]" class="file-input file-input-bordered w-full" accept="image/*,video/*" multiple />
      </div>

      <details class="collapse bg-base-200">
        <summary class="collapse-title font-semibold text-base cursor-pointer">
          Legacy date-time selection
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

      function toggleBookingMode() {
        const mode = document.querySelector('input[name="booking_mode"]:checked').value;
        document.getElementById('scheduled-booking-section').classList.toggle('hidden', mode !== 'scheduled');
      }

      function toggleRecurringFields() {
        const recurrenceType = document.querySelector('select[name="recurrence_type"]').value;
        document.getElementById('recurrence-end-date').classList.toggle('hidden', recurrenceType === 'none');
      }

      function togglePaymentSplit() {
        const splitType = document.getElementById('payment_split_type').value;
        document.getElementById('upfront-payment-wrap').classList.toggle('hidden', splitType !== 'partial');
      }

      function toggleAddressSource() {
        const selected = document.querySelector('input[name="service_address_source"]:checked');
        const savedBlock = document.getElementById('saved-address-block');
        const manualBlock = document.getElementById('manual-address-block');

        if (!selected) {
          return;
        }

        const usingSaved = selected.value === 'saved';
        if (savedBlock) {
          savedBlock.classList.toggle('hidden', !usingSaved);
        }
        if (manualBlock) {
          manualBlock.classList.toggle('hidden', usingSaved);
        }
      }

      function fillSavedAddress() {
        const select = document.getElementById('saved_address_id');
        if (!select || !select.selectedOptions.length) {
          return;
        }

        const option = select.selectedOptions[0];
        const fields = {
          service_address_label: option.dataset.label,
          service_address_line1: option.dataset.line1,
          service_address_line2: option.dataset.line2,
          service_city: option.dataset.city,
          service_area: option.dataset.area,
          service_postal_code: option.dataset.postalCode,
        };

        Object.entries(fields).forEach(([name, value]) => {
          const input = document.querySelector(`[name="${name}"]`);
          if (input) {
            input.value = value || '';
          }
        });
      }

      toggleBookingMode();
      toggleRecurringFields();
      togglePaymentSplit();
      toggleAddressSource();
      fillSavedAddress();
    </script>
  </div>
</section>

@endsection

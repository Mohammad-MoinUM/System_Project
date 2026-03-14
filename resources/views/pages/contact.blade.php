@extends('layouts.app')

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-primary/10 to-secondary/10">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 text-center">
    <h1 class="text-4xl font-bold text-base-content scroll-fade-up">Contact Us</h1>
    <p class="mt-3 text-lg text-base-content/60 max-w-2xl mx-auto scroll-fade-up" style="transition-delay:.1s">Have a question or feedback? We'd love to hear from you.</p>
  </div>
</section>

{{-- Contact Form --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="rounded-2xl border border-base-200 bg-base-100 p-8 shadow-sm scroll-fade-up">
      @if(session('success'))
        <div class="alert alert-success mb-6">{{ session('success') }}</div>
      @endif

      <form method="POST" action="{{ route('contact.store') }}">
        @csrf
        <div class="space-y-6">
          <div class="grid gap-6 sm:grid-cols-2">
            <div>
              <label class="label"><span class="label-text font-semibold">Name</span></label>
              <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" class="input input-bordered w-full" required />
              @error('name') <span class="text-error text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
              <label class="label"><span class="label-text font-semibold">Email</span></label>
              <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" class="input input-bordered w-full" required />
              @error('email') <span class="text-error text-sm">{{ $message }}</span> @enderror
            </div>
          </div>

          <div>
            <label class="label"><span class="label-text font-semibold">Subject</span></label>
            <input type="text" name="subject" value="{{ old('subject') }}" class="input input-bordered w-full" required />
            @error('subject') <span class="text-error text-sm">{{ $message }}</span> @enderror
          </div>

          <div>
            <label class="label"><span class="label-text font-semibold">Message</span></label>
            <textarea name="message" rows="5" class="textarea textarea-bordered w-full" required>{{ old('message') }}</textarea>
            @error('message') <span class="text-error text-sm">{{ $message }}</span> @enderror
          </div>

          <button type="submit" class="btn btn-primary">Send Message</button>
        </div>
      </form>
    </div>

    {{-- Contact Info --}}
    <div class="mt-12 grid gap-6 sm:grid-cols-3">
      <div class="text-center scroll-fade-up" style="transition-delay:.1s">
        <div class="mx-auto mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
          <x-heroicon-o-envelope class="h-6 w-6" />
        </div>
        <h3 class="font-bold text-base-content">Email</h3>
        <p class="text-sm text-base-content/60">support@haalchaal.com</p>
      </div>
      <div class="text-center scroll-fade-up" style="transition-delay:.2s">
        <div class="mx-auto mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
          <x-heroicon-o-phone class="h-6 w-6" />
        </div>
        <h3 class="font-bold text-base-content">Phone</h3>
        <p class="text-sm text-base-content/60">+880 1XXX-XXXXXX</p>
      </div>
      <div class="text-center scroll-fade-up" style="transition-delay:.3s">
        <div class="mx-auto mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
          <x-heroicon-o-map-pin class="h-6 w-6" />
        </div>
        <h3 class="font-bold text-base-content">Location</h3>
        <p class="text-sm text-base-content/60">Dhaka, Bangladesh</p>
      </div>
    </div>
  </div>
</section>

@endsection

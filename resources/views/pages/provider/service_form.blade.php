@extends('layouts.app')

@section('content')

<section class="bg-base-200">
  <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('provider.services.index') }}" class="inline-flex items-center gap-1.5 text-sm text-base-content/60 hover:text-base-content mb-6 transition-colors">
      <x-heroicon-o-arrow-left class="w-4 h-4" />
      Back to Services
    </a>

    <h1 class="text-3xl font-bold text-base-content">{{ $service ? 'Edit Service' : 'Add New Service' }}</h1>
    <p class="mt-2 text-base-content/60">{{ $service ? 'Update your service details.' : 'Create a new service that customers can book.' }}</p>

    @if($errors->any())
      <div class="alert alert-error mt-4">
        <ul class="list-disc list-inside">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST"
          action="{{ $service ? route('provider.services.update', $service) : route('provider.services.store') }}"
          class="mt-8 space-y-6">
      @csrf
      @if($service) @method('PUT') @endif

      <div>
        <label class="label"><span class="label-text font-semibold">Service Name</span></label>
        <input type="text" name="name" value="{{ old('name', $service->name ?? '') }}"
               class="input input-bordered w-full" placeholder="e.g. Home Cleaning" required />
        @error('name') <span class="text-error text-sm">{{ $message }}</span> @enderror
      </div>

      <div>
        <label class="label"><span class="label-text font-semibold">Category</span></label>
        <input type="text" name="category" value="{{ old('category', $service->category ?? '') }}"
               class="input input-bordered w-full" placeholder="e.g. Cleaning" required />
        @error('category') <span class="text-error text-sm">{{ $message }}</span> @enderror
      </div>

      <div>
        <label class="label"><span class="label-text font-semibold">Description</span></label>
        <textarea name="description" rows="4" class="textarea textarea-bordered w-full"
                  placeholder="Describe what this service includes...">{{ old('description', $service->description ?? '') }}</textarea>
        @error('description') <span class="text-error text-sm">{{ $message }}</span> @enderror
      </div>

      <div>
        <label class="label"><span class="label-text font-semibold">Price (BDT)</span></label>
        <input type="number" name="price" value="{{ old('price', $service->price ?? '') }}"
               class="input input-bordered w-full" step="0.01" min="0" placeholder="0.00" required />
        @error('price') <span class="text-error text-sm">{{ $message }}</span> @enderror
      </div>

      <div class="form-control">
        <label class="cursor-pointer label justify-start gap-3">
          <input type="hidden" name="is_active" value="0" />
          <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary"
                 {{ old('is_active', $service->is_active ?? true) ? 'checked' : '' }} />
          <span class="label-text font-semibold">Active (visible to customers)</span>
        </label>
      </div>

      <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">{{ $service ? 'Update Service' : 'Create Service' }}</button>
        <a href="{{ route('provider.services.index') }}" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</section>

@endsection

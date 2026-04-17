@extends('layouts.app')

@section('content')
<section class="bg-base-200">
  <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Portfolio & Work Gallery</h1>
    <p class="mt-2 text-base-content/60">Upload before/after or showcase photos of your completed work.</p>

    @if(session('success'))
      <div class="alert alert-success mt-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-error mt-4">{{ session('error') }}</div>
    @endif

    <div class="mt-8 rounded-2xl border border-base-300 bg-base-100 p-6">
      <h2 class="text-xl font-semibold">Add New Portfolio Item</h2>
      <form method="POST" action="{{ route('provider.portfolio.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-4 md:grid-cols-2">
        @csrf
        <input name="title" class="input input-bordered w-full md:col-span-2" placeholder="Title (optional)">
        <textarea name="description" class="textarea textarea-bordered w-full md:col-span-2" rows="3" placeholder="Describe this work"></textarea>
        <input name="job_date" type="date" class="input input-bordered w-full">
        <label class="label cursor-pointer justify-start gap-2">
          <input type="checkbox" name="is_public" value="1" class="checkbox checkbox-sm" checked>
          <span class="label-text">Visible publicly</span>
        </label>

        <div>
          <label class="label"><span class="label-text">Cover Image</span></label>
          <input name="cover_image" type="file" accept="image/*" class="file-input file-input-bordered w-full">
        </div>
        <div>
          <label class="label"><span class="label-text">Before Image</span></label>
          <input name="before_image" type="file" accept="image/*" class="file-input file-input-bordered w-full">
        </div>
        <div>
          <label class="label"><span class="label-text">After Image</span></label>
          <input name="after_image" type="file" accept="image/*" class="file-input file-input-bordered w-full">
        </div>

        <div class="md:col-span-2">
          <button type="submit" class="btn btn-primary btn-sm">Upload Item</button>
        </div>
      </form>
    </div>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      @forelse($items as $item)
        <article class="rounded-2xl border border-base-300 bg-base-100 p-4">
          @if($item->cover_image_path)
            <img src="{{ asset('storage/' . $item->cover_image_path) }}" alt="Portfolio cover" class="h-48 w-full rounded-xl object-cover">
          @elseif($item->before_image_path)
            <img src="{{ asset('storage/' . $item->before_image_path) }}" alt="Before" class="h-48 w-full rounded-xl object-cover">
          @elseif($item->after_image_path)
            <img src="{{ asset('storage/' . $item->after_image_path) }}" alt="After" class="h-48 w-full rounded-xl object-cover">
          @endif

          <h3 class="mt-4 text-lg font-semibold">{{ $item->title ?: 'Untitled Work' }}</h3>
          @if($item->description)
            <p class="mt-1 text-sm text-base-content/70">{{ $item->description }}</p>
          @endif
          <div class="mt-3 text-xs text-base-content/60">
            <span>{{ $item->job_date ? $item->job_date->format('M j, Y') : 'Date not set' }}</span>
            <span class="mx-2">•</span>
            <span>{{ $item->is_public ? 'Public' : 'Private' }}</span>
          </div>

          <form method="POST" action="{{ route('provider.portfolio.destroy', $item) }}" class="mt-4">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-error btn-outline btn-xs">Delete</button>
          </form>
        </article>
      @empty
        <p class="text-base-content/50 sm:col-span-2 lg:col-span-3">No portfolio items yet.</p>
      @endforelse
    </div>
  </div>
</section>
@endsection

@extends('layouts.app')

@section('content')
<section class="bg-base-200 min-h-screen">
  <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Work Portfolio</h1>
    <p class="mt-2 text-base-content/60">Showcase before/after transformations to build trust with customers.</p>

    @if(session('success'))
      <div class="alert alert-success mt-4">{{ session('success') }}</div>
    @endif

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
      <div class="rounded-2xl border border-base-300 bg-base-100 p-5 lg:col-span-1">
        <h2 class="text-lg font-bold">Add Portfolio Item</h2>
        <form method="POST" action="{{ route('provider.portfolio.store') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
          @csrf
          <input type="text" name="title" class="input input-bordered w-full" placeholder="Title (optional)">
          <textarea name="description" rows="3" class="textarea textarea-bordered w-full" placeholder="Describe this project"></textarea>
          <input type="date" name="job_date" class="input input-bordered w-full">

          <div>
            <label class="label"><span class="label-text">Cover Image</span></label>
            <input type="file" name="cover_image" class="file-input file-input-bordered w-full" accept="image/*">
          </div>
          <div>
            <label class="label"><span class="label-text">Before Image</span></label>
            <input type="file" name="before_image" class="file-input file-input-bordered w-full" accept="image/*">
          </div>
          <div>
            <label class="label"><span class="label-text">After Image</span></label>
            <input type="file" name="after_image" class="file-input file-input-bordered w-full" accept="image/*">
          </div>

          <label class="label cursor-pointer justify-start gap-2">
            <input type="checkbox" name="is_public" value="1" class="checkbox checkbox-sm" checked>
            <span class="label-text">Publicly visible</span>
          </label>

          <button class="btn btn-primary w-full">Save Item</button>
        </form>
      </div>

      <div class="lg:col-span-2 grid gap-4 sm:grid-cols-2">
        @forelse($items as $item)
          <div class="rounded-2xl border border-base-300 bg-base-100 p-4">
            @if($item->cover_image_path)
              <img src="{{ asset('storage/' . $item->cover_image_path) }}" alt="Portfolio" class="h-36 w-full rounded-xl object-cover">
            @endif
            <h3 class="mt-3 font-semibold">{{ $item->title ?: 'Untitled Work' }}</h3>
            @if($item->description)
              <p class="mt-1 text-sm text-base-content/60">{{ $item->description }}</p>
            @endif
            <div class="mt-2 flex gap-2 text-xs text-base-content/60">
              @if($item->job_date)
                <span class="badge badge-ghost">{{ $item->job_date->format('d M Y') }}</span>
              @endif
              <span class="badge {{ $item->is_public ? 'badge-success' : 'badge-warning' }}">{{ $item->is_public ? 'Public' : 'Private' }}</span>
            </div>

            <div class="mt-3 flex gap-2">
              @if($item->before_image_path)
                <a href="{{ asset('storage/' . $item->before_image_path) }}" target="_blank" class="btn btn-xs btn-outline">Before</a>
              @endif
              @if($item->after_image_path)
                <a href="{{ asset('storage/' . $item->after_image_path) }}" target="_blank" class="btn btn-xs btn-outline">After</a>
              @endif
              <form method="POST" action="{{ route('provider.portfolio.destroy', $item) }}" class="ml-auto" onsubmit="return confirm('Delete this item?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-xs btn-error">Delete</button>
              </form>
            </div>
          </div>
        @empty
          <div class="rounded-2xl border border-dashed border-base-300 p-8 text-center text-base-content/50 sm:col-span-2">
            No portfolio items yet.
          </div>
        @endforelse
      </div>
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
  </div>
</section>
@endsection

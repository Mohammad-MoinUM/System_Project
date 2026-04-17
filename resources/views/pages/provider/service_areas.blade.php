@extends('layouts.app')

@section('content')
<section class="bg-base-200">
  <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-base-content">Service Areas</h1>
    <p class="mt-2 text-base-content/60">Define zones where you accept jobs.</p>

    @if(session('success'))
      <div class="alert alert-success mt-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-error mt-4">{{ session('error') }}</div>
    @endif

    <div class="mt-8 rounded-2xl border border-base-300 bg-base-100 p-6">
      <h2 class="text-xl font-semibold">Add Area</h2>
      <form method="POST" action="{{ route('provider.service-areas.store') }}" class="mt-4 grid gap-4 md:grid-cols-3">
        @csrf
        <input name="city" class="input input-bordered w-full" placeholder="City" value="{{ old('city') }}" required>
        <input name="area_name" class="input input-bordered w-full" placeholder="Area / Zone" value="{{ old('area_name') }}" required>
        <label class="label cursor-pointer justify-start gap-2 md:justify-end">
          <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm" checked>
          <span class="label-text">Active</span>
        </label>
        <div class="md:col-span-3">
          <button type="submit" class="btn btn-primary btn-sm">Save Area</button>
        </div>
      </form>
    </div>

    <div class="mt-8 overflow-x-auto rounded-2xl border border-base-300 bg-base-100">
      <table class="table w-full">
        <thead>
          <tr>
            <th>City</th>
            <th>Area</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($areas as $area)
            <tr>
              <td>{{ $area->city }}</td>
              <td>{{ $area->area_name }}</td>
              <td>
                <span class="badge {{ $area->is_active ? 'badge-success' : 'badge-ghost' }}">
                  {{ $area->is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="text-right">
                <form method="POST" action="{{ route('provider.service-areas.destroy', $area) }}" class="inline">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-error btn-outline btn-xs" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-base-content/50">No service areas added yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>
@endsection

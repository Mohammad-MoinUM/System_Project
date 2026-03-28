@extends('admin.layouts.app')

@section('title', 'Services Management')

@section('content')
<div class="card bg-base-100 shadow-lg">
    <div class="card-body">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <h2 class="card-title font-semibold text-xl">All Services ({{ $services->total() }})</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <form action="{{ route('admin.services.index') }}" method="GET" class="md:col-span-2">
                <input type="text" name="search" placeholder="Search by name or provider..." 
                    value="{{ $search }}" class="input input-bordered w-full" />
            </form>
            <select name="category" class="select select-bordered" onchange="window.location='{{ route('admin.services.index') }}?category=' + this.value">
                <option value="" @if($category_filter === '') selected @endif>All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @if($category_filter === $category) selected @endif>{{ $category }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr class="text-base-content">
                        <th>Name</th>
                        <th>Provider</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td class="font-semibold">{{ $service->name }}</td>
                            <td>{{ $service->provider->name }}</td>
                            <td>{{ $service->category }}</td>
                            <td class="font-semibold">{{ $service->price }} BDT</td>
                            <td>
                                <span class="badge @if($service->is_active) badge-success @else badge-error @endif">
                                    {{ $service->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $service->created_at->format('M d, Y') }}</td>
                            <td class="space-x-2">
                                <a href="{{ route('admin.services.show', $service) }}" class="btn btn-xs btn-ghost">View</a>
                                <form action="{{ route('admin.services.toggle', $service) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-xs @if($service->is_active) btn-warning @else btn-success @endif">
                                        {{ $service->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <button onclick="deleteService({{ $service->id }})" class="btn btn-xs btn-error">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-base-content/60">No services found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $services->links() }}
        </div>
    </div>
</div>

<script>
function deleteService(serviceId) {
    if (confirm('Are you sure you want to delete this service?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.services.destroy", ":id") }}'.replace(':id', serviceId);
        form.innerHTML = '<input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection

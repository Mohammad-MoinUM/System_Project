@extends('admin.layouts.app')

@section('title', $service->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="card-title text-2xl font-bold">{{ $service->name }}</h2>
                        <p class="text-base-content/60">{{ $service->category }}</p>
                    </div>
                    <span class="badge badge-lg @if($service->is_active) badge-success @else badge-error @endif">
                        {{ $service->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="divider"></div>

                <div class="space-y-6">
                    <div>
                        <h3 class="font-semibold text-lg mb-3">Price & Details</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-base-content/60">Price</p>
                                <p class="font-semibold text-2xl text-primary">{{ $service->price }} BDT</p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">Created</p>
                                <p class="font-semibold">{{ $service->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg mb-3">Description</h3>
                        <p class="text-base-content">{{ $service->description ?? 'No description provided' }}</p>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="card-actions">
                    <form action="{{ route('admin.services.toggle', $service) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn @if($service->is_active) btn-warning @else btn-success @endif btn-sm">
                            {{ $service->is_active ? 'Disable Service' : 'Enable Service' }}
                        </button>
                    </form>
                    <button onclick="deleteService()" class="btn btn-error btn-sm">Delete Service</button>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title font-semibold mb-4">Provider</h3>
                <p class="font-semibold text-lg">{{ $service->provider->name }}</p>
                <p class="text-base-content/60">{{ $service->provider->email }}</p>
                <p class="text-base-content/60">{{ $service->provider->expertise ?? 'N/A' }}</p>
                <a href="{{ route('admin.users.show', $service->provider) }}" class="btn btn-ghost btn-sm mt-4">View Provider</a>
            </div>
        </div>
    </div>
</div>

<script>
function deleteService() {
    if (confirm('Are you sure you want to delete this service?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.services.destroy", $service) }}';
        form.innerHTML = '<input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection

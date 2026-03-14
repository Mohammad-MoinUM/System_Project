<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * List provider's services.
     */
    public function index(): View
    {
        $services = Service::where('provider_id', Auth::id())
            ->withCount('bookings')
            ->latest()
            ->get();

        return view('pages.provider.services', compact('services'));
    }

    /**
     * Show the create service form.
     */
    public function create(): View
    {
        return view('pages.provider.service_form', ['service' => null]);
    }

    /**
     * Store a new service.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'category'    => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'is_active'   => 'boolean',
        ]);

        $validated['provider_id'] = Auth::id();
        $validated['is_active'] = $request->boolean('is_active', true);

        Service::create($validated);

        return redirect()->route('provider.services.index')
                         ->with('success', 'Service created successfully.');
    }

    /**
     * Show the edit service form.
     */
    public function edit(Service $service): View
    {
        if ($service->provider_id !== Auth::id()) {
            abort(403);
        }

        return view('pages.provider.service_form', compact('service'));
    }

    /**
     * Update a service.
     */
    public function update(Request $request, Service $service): RedirectResponse
    {
        if ($service->provider_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'category'    => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $service->update($validated);

        return redirect()->route('provider.services.index')
                         ->with('success', 'Service updated successfully.');
    }

    /**
     * Delete a service.
     */
    public function destroy(Service $service): RedirectResponse
    {
        if ($service->provider_id !== Auth::id()) {
            abort(403);
        }

        $service->delete();

        return redirect()->route('provider.services.index')
                         ->with('success', 'Service deleted.');
    }

    /**
     * Toggle service active status.
     */
    public function toggle(Service $service): RedirectResponse
    {
        if ($service->provider_id !== Auth::id()) {
            abort(403);
        }

        $service->update(['is_active' => !$service->is_active]);

        return back()->with('success', 'Service status updated.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class AdminServiceController extends Controller
{
    /**
     * Show all services
     */
    public function index(Request $request): View
    {
        $query = Service::with('provider');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%")
                  ->orWhereHas('provider', fn($q) => $q->where('name', 'like', "%$search%"));
        }

        $services = $query->orderBy('created_at', 'desc')->paginate(20);

        $categories = Service::distinct()->pluck('category')->sort();

        return view('admin.services.index', [
            'services' => $services,
            'categories' => $categories,
            'search' => $request->search ?? '',
            'category_filter' => $request->category ?? '',
        ]);
    }

    /**
     * Show service details
     */
    public function show(Service $service): View
    {
        return view('admin.services.show', ['service' => $service]);
    }

    /**
     * Toggle service active status
     */
    public function toggle(Service $service): RedirectResponse
    {
        $service->update(['is_active' => !$service->is_active]);

        return redirect()->route('admin.services.show', $service)
            ->with('success', 'Service status updated.');
    }

    /**
     * Delete service
     */
    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Service deleted.');
    }
}

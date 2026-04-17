<?php

namespace App\Http\Controllers;

use App\Models\ProviderServiceArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProviderServiceAreaController extends Controller
{
    public function index(): View
    {
        $areas = ProviderServiceArea::where('user_id', Auth::id())
            ->orderByDesc('is_active')
            ->orderBy('city')
            ->orderBy('area_name')
            ->get();

        return view('pages.provider.service_areas', compact('areas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'city' => ['required', 'string', 'max:120'],
            'area_name' => ['required', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ProviderServiceArea::create([
            'user_id' => Auth::id(),
            'city' => trim($data['city']),
            'area_name' => trim($data['area_name']),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('success', 'Service area added.');
    }

    public function update(Request $request, ProviderServiceArea $serviceArea): RedirectResponse
    {
        if ($serviceArea->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'city' => ['required', 'string', 'max:120'],
            'area_name' => ['required', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $serviceArea->update([
            'city' => trim($data['city']),
            'area_name' => trim($data['area_name']),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return back()->with('success', 'Service area updated.');
    }

    public function destroy(ProviderServiceArea $serviceArea): RedirectResponse
    {
        if ($serviceArea->user_id !== Auth::id()) {
            abort(403);
        }

        $serviceArea->delete();

        return back()->with('success', 'Service area removed.');
    }
}

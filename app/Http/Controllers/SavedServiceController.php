<?php

namespace App\Http\Controllers;

use App\Models\SavedService;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SavedServiceController extends Controller
{
    public function index(): View
    {
        $savedServices = SavedService::where('taker_id', Auth::id())
            ->with(['service.provider'])
            ->latest()
            ->paginate(12);

        return view('pages.saved_services', compact('savedServices'));
    }

    public function toggle(Service $service): RedirectResponse
    {
        $existing = SavedService::where('taker_id', Auth::id())
            ->where('service_id', $service->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return back()->with('success', 'Service removed from wishlist.');
        }

        SavedService::create([
            'taker_id' => Auth::id(),
            'service_id' => $service->id,
        ]);

        return back()->with('success', 'Service added to wishlist.');
    }
}

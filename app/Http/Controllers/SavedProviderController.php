<?php

namespace App\Http\Controllers;

use App\Models\SavedProvider;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SavedProviderController extends Controller
{
    /**
     * List saved providers for the current customer.
     */
    public function index(): View
    {
        $savedProviders = SavedProvider::where('taker_id', Auth::id())
            ->with('provider:id,first_name,last_name,name,photo,city,area,bio,expertise,experience_years')
            ->latest()
            ->get();

        return view('pages.saved_providers', compact('savedProviders'));
    }

    /**
     * Save a provider (toggle).
     */
    public function store(User $provider): RedirectResponse
    {
        if ($provider->role !== 'provider') {
            return back()->with('error', 'Invalid provider.');
        }

        if ($provider->id === Auth::id()) {
            return back()->with('error', 'You cannot save yourself.');
        }

        $existing = SavedProvider::where('taker_id', Auth::id())
            ->where('provider_id', $provider->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return back()->with('success', 'Provider removed from saved list.');
        }

        SavedProvider::create([
            'taker_id'    => Auth::id(),
            'provider_id' => $provider->id,
        ]);

        return back()->with('success', 'Provider saved!');
    }

    /**
     * Remove a saved provider.
     */
    public function destroy(User $provider): RedirectResponse
    {
        SavedProvider::where('taker_id', Auth::id())
            ->where('provider_id', $provider->id)
            ->delete();

        return back()->with('success', 'Provider removed from saved list.');
    }
}

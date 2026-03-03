<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class ProviderPageController extends Controller
{
    public function jobs(): RedirectResponse
    {
        return redirect()->route('provider.dashboard');
    }

    public function earnings(): RedirectResponse
    {
        return redirect()->route('provider.dashboard');
    }

    public function reviews(): RedirectResponse
    {
        return redirect()->route('provider.dashboard');
    }

    public function schedule(): RedirectResponse
    {
        return redirect()->route('provider.dashboard');
    }

    public function analytics(): RedirectResponse
    {
        return redirect()->route('provider.dashboard');
    }

    public function settings(): RedirectResponse
    {
        return redirect()->route('provider.dashboard');
    }
}

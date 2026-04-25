<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProviderVerificationController extends Controller
{
    /**
     * Show pending verification page
     */
    public function pending(): View
    {
        $user = auth()->user();
        
        // Redirect if not pending
        if ($user->role !== 'provider' || $user->verification_status === 'approved') {
            return redirect()->route('provider.dashboard');
        }

        if ($user->verification_status === 'rejected') {
            return redirect()->route('provider.verification-rejected');
        }

        return view('provider.verification-pending', ['user' => $user]);
    }

    /**
     * Show rejection page
     */
    public function rejected(): View
    {
        $user = auth()->user();

        if ($user->role !== 'provider' || $user->verification_status !== 'rejected') {
            return redirect()->route('home');
        }

        return view('provider.verification-rejected', ['user' => $user]);
    }

    /**
     * Logout and return to login
     */
    public function logout(): RedirectResponse
    {
        auth()->logout();
        return redirect()->route('login')->with('info', 'You have been logged out.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class AdminProviderVerificationController extends Controller
{
    /**
     * List pending provider verifications
     */
    public function pending(): View
    {
        $providers = User::where('role', 'provider')
            ->where('verification_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.providers.pending', ['providers' => $providers]);
    }

    /**
     * Show provider details for verification
     */
    public function show(User $provider): View
    {
        if ($provider->role !== 'provider') {
            abort(404);
        }

        $stats = [
            'bookings' => $provider->bookingsAsProvider()->count(),
            'services' => $provider->servicesProvided()->count(),
            'reviews' => $provider->reviewsReceived()->count(),
        ];

        return view('admin.providers.show', [
            'provider' => $provider,
            'stats' => $stats,
        ]);
    }

    /**
     * Approve provider
     */
    public function approve(Request $request, User $provider): RedirectResponse
    {
        if ($provider->role !== 'provider') {
            abort(404);
        }

        $provider->update([
            'verification_status' => 'approved',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
            'rejection_reason' => null,
        ]);

        return redirect()->route('admin.providers.pending')
            ->with('success', "Provider '{$provider->name}' has been approved successfully!");
    }

    /**
     * Reject provider
     */
    public function reject(Request $request, User $provider): RedirectResponse
    {
        if ($provider->role !== 'provider') {
            abort(404);
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $provider->update([
            'verification_status' => 'rejected',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('admin.providers.pending')
            ->with('success', "Provider '{$provider->name}' has been rejected.");
    }

    /**
     * Show all verified providers
     */
    public function approved(): View
    {
        $providers = User::where('role', 'provider')
            ->where('verification_status', 'approved')
            ->orderBy('verified_at', 'desc')
            ->paginate(20);

        return view('admin.providers.approved', ['providers' => $providers]);
    }

    /**
     * Show all rejected providers
     */
    public function rejected(): View
    {
        $providers = User::where('role', 'provider')
            ->where('verification_status', 'rejected')
            ->orderBy('verified_at', 'desc')
            ->paginate(20);

        return view('admin.providers.rejected', ['providers' => $providers]);
    }
}

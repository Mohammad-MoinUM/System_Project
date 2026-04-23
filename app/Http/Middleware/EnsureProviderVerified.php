<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // TEMPORARILY PAUSED: Provider verification checks disabled for testing
        // Only apply to providers
        /*if ($user && $user->role === 'provider') {
            // If provider is not verified, redirect to pending page
            if ($user->verification_status !== 'approved') {
                return redirect()->route('provider.verification-pending');
            }

            // If rejected, show rejection page
            if ($user->verification_status === 'rejected') {
                return redirect()->route('provider.verification-rejected');
            }
        }*/

        return $next($request);
    }
}

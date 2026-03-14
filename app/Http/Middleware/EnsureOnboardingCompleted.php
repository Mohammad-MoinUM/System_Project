<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->onboarding_completed) {
            $route = match ($user->role) {
                'provider' => 'onboarding.provider',
                'customer' => 'onboarding.customer',
                default => 'home',
            };

            return redirect()->route($route);
        }

        return $next($request);
    }
}

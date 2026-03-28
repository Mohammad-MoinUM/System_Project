<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCorporateAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'customer') {
            abort(403, 'Access denied');
        }

        // Check if user is part of any company
        if ($user->companyMemberships()->where('is_active', true)->doesntExist()) {
            abort(403, 'You are not part of any company');
        }

        return $next($request);
    }
}

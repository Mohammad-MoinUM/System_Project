<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMobileToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Unauthenticated. Missing bearer token.',
            ], 401);
        }

        $tokenHash = hash('sha256', $token);

        $user = User::where('mobile_api_token_hash', $tokenHash)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated. Invalid token.',
            ], 401);
        }

        $request->setUserResolver(static fn () => $user);

        return $next($request);
    }
}
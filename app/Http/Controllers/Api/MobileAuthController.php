<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class MobileAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', 'in:provider,customer'],
            'referral_code' => ['nullable', 'string', 'max:20'],
        ]);

        $referredByUserId = null;
        if (!empty($data['referral_code'])) {
            $referrer = User::where('referral_code', trim($data['referral_code']))->first();

            if (!$referrer) {
                return response()->json([
                    'message' => 'The referral code you entered is invalid.',
                ], 422);
            }

            $referredByUserId = $referrer->id;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
            'referred_by_user_id' => $referredByUserId,
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Registration successful. Please verify your email before logging in.',
            'must_verify_email' => true,
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'role' => ['required', 'in:provider,customer,admin'],
        ]);

        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 422);
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->role !== $data['role']) {
            Auth::logout();

            return response()->json([
                'message' => 'This account does not match the selected role.',
            ], 422);
        }

        if (!$user->hasVerifiedEmail()) {
            Auth::logout();

            return response()->json([
                'message' => 'Please verify your email address before continuing.',
                'must_verify_email' => true,
            ], 403);
        }

        $plainToken = Str::random(80);
        $user->forceFill([
            'mobile_api_token_hash' => hash('sha256', $plainToken),
            'mobile_api_token_created_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Login successful.',
            'session' => [
                'token' => $plainToken,
                'user' => $this->userPayload($user),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user) {
            $user->forceFill([
                'mobile_api_token_hash' => null,
                'mobile_api_token_created_at' => null,
            ])->save();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'user' => $this->userPayload($user),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'name' => trim($data['name']),
            'phone' => $data['phone'] ?? null,
            'city' => $data['city'] ?? null,
        ]);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $this->userPayload($user->fresh()),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'city' => $user->city,
            'role' => $user->role,
            'onboarding_completed' => (bool) $user->onboarding_completed,
            'email_verified_at' => optional($user->email_verified_at)?->toIso8601String(),
        ];
    }
}
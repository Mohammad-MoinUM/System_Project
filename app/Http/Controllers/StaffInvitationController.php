<?php

namespace App\Http\Controllers;

use App\Models\CompanyUserMembership;
use App\Models\StaffInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class StaffInvitationController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $invitation = StaffInvitation::with('company')
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return redirect()->route('login')->with('error', 'This invitation link is invalid or expired.');
        }

        if (Auth::check() && strcasecmp(Auth::user()->email, $invitation->email) !== 0) {
            return redirect()->route('home')->with('error', 'This invitation is for a different email address.');
        }

        $hasAccount = User::where('email', $invitation->email)->exists();

        return view('auth.accept_staff_invitation', compact('invitation', 'hasAccount'));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = StaffInvitation::with('company')
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return redirect()->route('login')->with('error', 'This invitation link is invalid or expired.');
        }

        $user = Auth::user();

        if ($user && strcasecmp($user->email, $invitation->email) !== 0) {
            return redirect()->route('home')->with('error', 'This invitation is for a different email address.');
        }

        if (!$user) {
            $existingUser = User::where('email', $invitation->email)->first();

            if ($existingUser) {
                return redirect()
                    ->route('login')
                    ->with('info', 'Please sign in with the invited email to accept this invitation.');
            } else {
                $validated = $request->validate([
                    'name' => ['required', 'string', 'max:255'],
                    'password' => ['required', 'confirmed', Password::min(8)],
                ]);

                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $invitation->email,
                    'role' => 'customer',
                    'onboarding_completed' => true,
                    'password' => $validated['password'],
                    'email_verified_at' => now(),
                ]);

                Auth::login($user);
            }
        }

        if (!$user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
            $user->save();
        }

        CompanyUserMembership::updateOrCreate(
            [
                'company_id' => $invitation->company_id,
                'user_id' => $user->id,
            ],
            [
                'branch_id' => $invitation->branch_id,
                'role' => $invitation->role,
                'is_active' => true,
                'invited_at' => $invitation->created_at,
                'joined_at' => now(),
            ]
        );

        $invitation->accepted_at = now();
        $invitation->token = null;
        $invitation->save();

        return redirect()
            ->route('corporate.dashboard')
            ->with('success', 'Invitation accepted successfully. Welcome to your company workspace.');
    }
}

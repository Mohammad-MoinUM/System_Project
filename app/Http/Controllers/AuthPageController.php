<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthPageController extends Controller
{
    /**
     * Show login page
     */
    public function login(): View
    {
        return view('auth.login');
    }

    /**
     * Show register page
     */
    public function register(): View
    {
        return view('auth.register');
    }

    /**
     * Handle login request
     */
    public function loginStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'role' => ['required', 'in:provider,customer,admin'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->role !== $data['role']) {
            Auth::logout();

            $roleLabel = $data['role'] === 'customer' ? 'customer' : $data['role'];
            return back()->withErrors([
                'email' => 'This account is not registered as ' . $roleLabel . '.',
            ])->onlyInput('email');
        }

        if (!$user->hasVerifiedEmail()) {
            return redirect()
                ->route('verification.notice')
                ->with('info', 'Please verify your email address before continuing.');
        }

        return $this->redirectByRole($user);
    }

    /**
     * Handle register request
     */
    public function registerStore(Request $request): RedirectResponse
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
                return back()
                    ->withErrors(['referral_code' => 'The referral code you entered is invalid.'])
                    ->onlyInput('name', 'email', 'role', 'referral_code');
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

        Auth::login($user);
        $user->sendEmailVerificationNotification();

        $request->session()->regenerate();

        return redirect()
            ->route('verification.notice')
            ->with('success', 'Registration successful. Please verify your email address.');
    }

    /**
     * Logout user
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirect user based on role
     */
    private function redirectByRole(User $user): RedirectResponse
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if (!$user->onboarding_completed) {
            return match ($user->role) {
                'provider' => redirect()->route('onboarding.provider'),
                'customer' => redirect()->route('onboarding.customer'),
                default => redirect()->route('home'),
            };
        }

        // If customer role with active company membership, go to corporate dashboard
        if ($user->role === 'customer' && $user->companyMemberships()->where('is_active', true)->exists()) {
            return redirect()->route('corporate.dashboard');
        }

        return match ($user->role) {
            'provider' => redirect()->route('provider.dashboard'),
            'customer' => redirect()->route('customer.dashboard'),
            default => redirect()->route('home'),
        };
    }
}
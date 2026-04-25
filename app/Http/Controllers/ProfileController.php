<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\LoyaltyTransaction;
use App\Models\Review;
use App\Models\SavedProvider;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\LoyaltyRewardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $stats = [];
        $addresses = UserAddress::where('user_id', $user->id)->latest()->get();

        if ($user->role === 'provider') {
            $stats['jobs_completed'] = Booking::where('provider_id', $user->id)->where('status', 'completed')->count();
            $stats['active_jobs'] = Booking::where('provider_id', $user->id)->whereIn('status', ['pending', 'active', 'in_progress', 'awaiting_confirmation'])->count();
            $stats['total_earnings'] = Booking::completedTotalWithTipsForUser('provider_id', $user->id);
            $stats['avg_rating'] = Review::where('provider_id', $user->id)->avg('rating');
            $stats['total_reviews'] = Review::where('provider_id', $user->id)->count();
            $stats['services_count'] = $user->servicesProvided()->where('is_active', true)->count();
            $stats['successful_referrals'] = User::where('referred_by_user_id', $user->id)->where('onboarding_completed', true)->count();
            $stats['referral_code'] = $user->referral_code;
            $stats['referral_credits_earned'] = (float) WalletTransaction::where('user_id', $user->id)
                ->where('type', 'referral_credit')
                ->sum('amount');
        } else {
            $stats['total_bookings'] = Booking::where('taker_id', $user->id)->count();
            $stats['active_bookings'] = Booking::where('taker_id', $user->id)->whereIn('status', ['active', 'in_progress', 'awaiting_confirmation'])->count();
            $stats['total_spent'] = Booking::completedTotalWithTipsForUser('taker_id', $user->id);
            $stats['reviews_given'] = Review::where('taker_id', $user->id)->count();
            $stats['saved_providers'] = SavedProvider::where('taker_id', $user->id)->count();
            $stats['loyalty_points'] = $user->loyalty_points ?? 0;
            $stats['saved_addresses'] = $addresses->count();
            $stats['successful_referrals'] = User::where('referred_by_user_id', $user->id)->where('onboarding_completed', true)->count();
            $stats['referral_code'] = $user->referral_code;
            $stats['referral_credits_earned'] = (float) WalletTransaction::where('user_id', $user->id)
                ->where('type', 'referral_credit')
                ->sum('amount');
        }

        return view('pages.profile', compact('user', 'stats', 'addresses'));
    }

    public function edit(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $addresses = UserAddress::where('user_id', $user->id)->latest()->get();

        return view('pages.profile_edit', compact('user', 'addresses'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'city'       => 'nullable|string|max:255',
            'area'       => 'nullable|string|max:255',
            'photo'      => 'nullable|image|max:2048',
        ];

        if ($user->role === 'provider') {
            $rules['bio'] = 'nullable|string|max:1000';
            $rules['expertise'] = 'nullable|string|max:255';
            $rules['experience_years'] = 'nullable|integer|min:0|max:50';
            $rules['alt_phone'] = 'nullable|string|max:20';
        } else {
            $rules['alt_phone'] = 'nullable|string|max:20';
            $rules['preferred_time_slots'] = 'nullable|array';
            $rules['preferred_time_slots.*'] = 'string|in:morning,afternoon,evening,weekend';
            $rules['provider_gender_preference'] = 'nullable|in:any,male,female';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $user->update($validated);

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_default')) {
            UserAddress::where('user_id', $user->id)->update(['is_default' => false]);
        }

        UserAddress::create([
            'user_id' => $user->id,
            'label' => $validated['label'],
            'line1' => $validated['line1'],
            'line2' => $validated['line2'] ?? null,
            'city' => $validated['city'] ?? null,
            'area' => $validated['area'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with('success', 'Saved address added successfully.');
    }

    public function setDefaultAddress(UserAddress $address): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($address->user_id !== $user->id) {
            abort(403);
        }

        UserAddress::where('user_id', $user->id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', 'Default address updated.');
    }

    public function destroyAddress(UserAddress $address): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($address->user_id !== $user->id) {
            abort(403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $nextAddress = UserAddress::where('user_id', $user->id)->oldest()->first();

            if ($nextAddress) {
                $nextAddress->update(['is_default' => true]);
            }
        }

        return back()->with('success', 'Address removed.');
    }

    public function redeemRewards(Request $request, LoyaltyRewardService $loyaltyRewardService): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'points' => ['required', 'integer', 'min:10'],
        ]);

        try {
            $result = $loyaltyRewardService->redeemPoints($user, (int) $validated['points']);
        } catch (\InvalidArgumentException | \RuntimeException $exception) {
            return back()->withErrors(['points' => $exception->getMessage()]);
        }

        return back()->with(
            'success',
            'Redeemed ' . $result['points'] . ' points for ' . number_format($result['credit_amount'], 2) . ' wallet credit.'
        );
    }
}

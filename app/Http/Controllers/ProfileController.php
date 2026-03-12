<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use App\Models\SavedProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = Auth::user();
        $stats = [];

        if ($user->role === 'provider') {
            $stats['jobs_completed'] = Booking::where('provider_id', $user->id)->where('status', 'completed')->count();
            $stats['active_jobs'] = Booking::where('provider_id', $user->id)->whereIn('status', ['pending', 'active', 'in_progress'])->count();
            $stats['total_earnings'] = (float) Booking::where('provider_id', $user->id)->where('status', 'completed')->sum('total');
            $stats['avg_rating'] = Review::where('provider_id', $user->id)->avg('rating');
            $stats['total_reviews'] = Review::where('provider_id', $user->id)->count();
            $stats['services_count'] = $user->servicesProvided()->where('is_active', true)->count();
        } else {
            $stats['total_bookings'] = Booking::where('taker_id', $user->id)->count();
            $stats['active_bookings'] = Booking::where('taker_id', $user->id)->where('status', 'active')->count();
            $stats['total_spent'] = (float) Booking::where('taker_id', $user->id)->where('status', 'completed')->sum('total');
            $stats['reviews_given'] = Review::where('taker_id', $user->id)->count();
            $stats['saved_providers'] = SavedProvider::where('taker_id', $user->id)->count();
        }

        return view('pages.profile', compact('user', 'stats'));
    }

    public function edit(): View
    {
        $user = Auth::user();
        return view('pages.profile_edit', compact('user'));
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
}

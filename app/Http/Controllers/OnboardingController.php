<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OnboardingController extends Controller
{
    public function customerForm()
    {
        return view('onboarding.customer_form');
    }

    public function customerStore(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'phone'      => ['required', 'string', 'max:20'],
            'alt_phone'  => ['nullable', 'string', 'max:20'],
            'city'       => ['required', 'string', 'max:255'],
            'area'       => ['required', 'string', 'max:255'],
            'photo'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        // Handle photo upload — stored locally, path saved as URL
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            $path = $request->file('photo')->store('photos', 'public');
            $validated['photo'] = $path;
        }

        $user->update([
            'first_name'           => $validated['first_name'],
            'last_name'            => $validated['last_name'],
            'name'                 => $validated['first_name'] . ' ' . $validated['last_name'],
            'phone'                => $validated['phone'],
            'alt_phone'            => $validated['alt_phone'] ?? null,
            'city'                 => $validated['city'],
            'area'                 => $validated['area'],
            'photo'                => $validated['photo'] ?? $user->photo,
            'onboarding_completed' => true,
        ]);

        return redirect()->route('customer.dashboard')
                         ->with('success', 'Profile completed successfully!');
    }

    public function providerForm()
    {
        return view('onboarding.provider_form');
    }

    public function providerStore(Request $request)
    {
        $validated = $request->validate([
            'first_name'        => ['required', 'string', 'max:255'],
            'last_name'         => ['required', 'string', 'max:255'],
            'phone'             => ['required', 'string', 'max:20'],
            'nid_number'        => ['required', 'string', 'max:30'],
            'city'              => ['required', 'string', 'max:255'],
            'area'              => ['required', 'string', 'max:255'],
            'education'         => ['required', 'string', 'max:255'],
            'institution'       => ['nullable', 'string', 'max:255'],
            'certifications'    => ['nullable', 'array'],
            'certifications.*'  => ['nullable', 'string', 'max:255'],
            'expertise'         => ['required', 'string', 'max:500'],
            'experience_years'  => ['required', 'integer', 'min:0', 'max:255'],
            'services_offered'  => ['required', 'array', 'min:1'],
            'services_offered.*'=> ['string', 'max:255'],
            'bio'               => ['nullable', 'string', 'max:2000'],
            'photo'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        // Filter out empty certification entries
        $certifications = array_values(array_filter($validated['certifications'] ?? [], fn ($c) => trim($c) !== ''));

        $user->update([
            'first_name'           => $validated['first_name'],
            'last_name'            => $validated['last_name'],
            'name'                 => $validated['first_name'] . ' ' . $validated['last_name'],
            'phone'                => $validated['phone'],
            'nid_number'           => $validated['nid_number'],
            'city'                 => $validated['city'],
            'area'                 => $validated['area'],
            'education'            => $validated['education'],
            'institution'          => $validated['institution'] ?? null,
            'certifications'       => $certifications ?: null,
            'expertise'            => $validated['expertise'],
            'experience_years'     => $validated['experience_years'],
            'services_offered'     => $validated['services_offered'],
            'bio'                  => $validated['bio'] ?? null,
            'photo'                => $validated['photo'] ?? $user->photo,
            'onboarding_completed' => true,
        ]);

        return redirect()->route('provider.dashboard')
                         ->with('success', 'Provider profile completed successfully!');
    }
}

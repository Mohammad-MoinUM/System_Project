<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CorporateRegistrationController extends Controller
{
    /**
     * Show corporate registration form
     */
    public function showRegistrationForm()
    {
        return view('corporate.register');
    }

    /**
     * Store new company registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'contact_person_name' => 'required|string|max:255',
            'company_registration_number' => 'required|string|unique:companies,company_registration_number',
            'company_documents' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            // Create corporate admin user
            $user = new \App\Models\User();
            $user->first_name = $validated['first_name'];
            $user->last_name = $validated['last_name'];
            $user->name = $validated['first_name'] . ' ' . $validated['last_name'];
            $user->email = $validated['email'];
            $user->phone = $validated['phone'];
            $user->password = bcrypt($validated['password']);
            $user->role = 'customer'; // Corporate admins are customer role
            $user->onboarding_completed = true;
            $user->save();

            // Handle document upload
            $documentPath = null;
            if ($request->hasFile('company_documents')) {
                $documentPath = $request->file('company_documents')
                    ->store('company_documents', 'public');
            }

            // Create company
            $company = Company::create([
                'company_name' => $validated['company_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'postal_code' => $validated['postal_code'],
                'contact_person_name' => $validated['contact_person_name'],
                'company_registration_number' => $validated['company_registration_number'],
                'company_documents_path' => $documentPath,
                'primary_admin_id' => $user->id,
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // Add user as admin in company
            $company->staff()->create([
                'user_id' => $user->id,
                'role' => 'admin',
                'is_active' => true,
                'joined_at' => now(),
            ]);

            // Log the user in
            Auth::login($user);

            return redirect()
                ->route('corporate.dashboard')
                ->with('success', 'Company registered and approved successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }
}

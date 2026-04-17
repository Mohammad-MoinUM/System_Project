<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    /**
     * Show form to create new admin
     */
    public function createAdmin(): View
    {
        return view('admin.create-admin');
    }

    /**
     * Store new admin user
     */
    public function storeAdmin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => 'admin',
            'password' => Hash::make($data['password']),
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'New admin user created successfully and verified. Email: ' . $data['email']);
    }
}

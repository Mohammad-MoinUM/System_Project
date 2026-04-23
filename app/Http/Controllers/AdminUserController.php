<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Show all users
     */
    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(15);

        return view('admin.users.index', [
            'users' => $users,
            'search' => $request->search ?? '',
            'role_filter' => $request->role ?? 'all',
        ]);
    }

    /**
     * Show user details
     */
    public function show(User $user): View
    {
        $stats = [
            'bookings' => $user->bookingsAsTaker()->count() + $user->bookingsAsProvider()->count(),
            'services' => $user->servicesProvided()->count(),
            'reviews_given' => $user->reviewsGiven()->count(),
            'reviews_received' => $user->reviewsReceived()->count(),
        ];

        return view('admin.users.show', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    /**
     * Edit user
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:admin,provider,customer'],
            'phone' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
        ]);

        if ($data['role'] === 'provider') {
            $data['onboarding_completed'] = true;

            if (!$user->email_verified_at) {
                $data['email_verified_at'] = now();
            }

            if ($user->verification_status !== 'approved') {
                $data['verification_status'] = 'approved';
                $data['verified_at'] = now();
                $data['verified_by'] = $request->user()?->id;
                $data['rejection_reason'] = null;
            }
        }

        $user->update($data);

        $message = 'User updated successfully.';

        if ($data['role'] === 'provider') {
            $message = 'Provider account updated successfully and is ready for dashboard access.';
        }

        return redirect()->route('admin.users.show', $user)->with('success', $message);
    }

    /**
     * Delete user
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('admin.users.show', $user)->with('success', 'Password reset successfully.');
    }
}

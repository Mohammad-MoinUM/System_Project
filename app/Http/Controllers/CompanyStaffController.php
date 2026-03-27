<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Models\CompanyUserMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CompanyStaffController extends Controller
{
    /**
     * Show all staff members
     */
    public function index($companyId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            !in_array($user->getRoleInCompany($companyId), ['admin', 'manager'])) {
            abort(403);
        }

        $staff = $company->staff()
            ->with('user', 'branch')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('corporate.staff.index', [
            'company' => $company,
            'staff' => $staff,
        ]);
    }

    /**
     * Show invite form
     */
    public function create($companyId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            $user->getRoleInCompany($companyId) !== 'admin') {
            abort(403);
        }

        $roles = [
            'admin' => 'Admin - Full access',
            'manager' => 'Manager - Manage branches',
            'requester' => 'Requester - Create service requests',
            'approver' => 'Approver - Approve requests',
            'finance' => 'Finance - View invoices',
        ];

        $branches = $company->branches()->where('is_active', true)->get();

        return view('corporate.staff.create', [
            'company' => $company,
            'roles' => $roles,
            'branches' => $branches,
        ]);
    }

    /**
     * Send staff invitation
     */
    public function inviteStaff(Request $request, $companyId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            $user->getRoleInCompany($companyId) !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:admin,manager,requester,approver,finance',
            'branch_id' => 'nullable|exists:company_branches,id',
        ]);

        // Check if user already exists
        $staffUser = User::where('email', $validated['email'])->first();

        if (!$staffUser) {
            // Create new user with temporary password
            $tempPassword = str()->random(12);
            $staffUser = User::create([
                'email' => $validated['email'],
                'name' => $validated['email'],
                'password' => bcrypt($tempPassword),
                'role' => 'customer',
                'onboarding_completed' => true,
            ]);

            // Send invitation email with temp password
            // Mail::send('emails.staff-invitation', [
            //     'company' => $company,
            //     'email' => $validated['email'],
            //     'tempPassword' => $tempPassword,
            // ], function ($message) use ($validated) {
            //     $message->to($validated['email']);
            // });
        }

        // Check if already a member
        $existing = $company->staff()
            ->where('user_id', $staffUser->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'User is already a staff member of this company.');
        }

        // Add as staff member
        $company->staff()->create([
            'user_id' => $staffUser->id,
            'role' => $validated['role'],
            'branch_id' => $validated['branch_id'] ?? null,
            'is_active' => true,
            'invited_at' => now(),
            'joined_at' => now(),
        ]);

        return back()->with('success', 'Staff member added successfully.');
    }

    /**
     * Show staff edit form
     */
    public function edit($companyId, $memberId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            $user->getRoleInCompany($companyId) !== 'admin') {
            abort(403);
        }

        $member = $company->staff()->findOrFail($memberId);
        $member->load('user', 'branch');

        $roles = [
            'admin' => 'Admin - Full access',
            'manager' => 'Manager - Manage branches',
            'requester' => 'Requester - Create service requests',
            'approver' => 'Approver - Approve requests',
            'finance' => 'Finance - View invoices',
        ];

        $branches = $company->branches()->where('is_active', true)->get();

        return view('corporate.staff.edit', [
            'company' => $company,
            'member' => $member,
            'roles' => $roles,
            'branches' => $branches,
        ]);
    }

    /**
     * Update staff member
     */
    public function update(Request $request, $companyId, $memberId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            $user->getRoleInCompany($companyId) !== 'admin') {
            abort(403);
        }

        $member = $company->staff()->findOrFail($memberId);

        $validated = $request->validate([
            'role' => 'required|in:admin,manager,requester,approver,finance',
            'branch_id' => 'nullable|exists:company_branches,id',
        ]);

        $member->update($validated);

        return redirect()
            ->route('corporate.staff.index', $company->id)
            ->with('success', 'Staff member updated successfully.');
    }

    /**
     * Remove staff member
     */
    public function destroy($companyId, $memberId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            $user->getRoleInCompany($companyId) !== 'admin') {
            abort(403);
        }

        $member = $company->staff()->findOrFail($memberId);
        $member->update(['is_active' => false]);

        return redirect()
            ->route('corporate.staff.index', $company->id)
            ->with('success', 'Staff member removed successfully.');
    }
}

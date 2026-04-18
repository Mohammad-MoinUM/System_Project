<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\StaffInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        if (!empty($validated['branch_id'])) {
            $company->branches()->where('id', $validated['branch_id'])->firstOrFail();
        }

        $staffUser = User::where('email', $validated['email'])->first();

        if ($staffUser && $company->staff()->where('user_id', $staffUser->id)->exists()) {
            return back()->with('error', 'User is already a staff member of this company.');
        }

        $pendingInvitation = StaffInvitation::where('company_id', $company->id)
            ->where('email', $validated['email'])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($pendingInvitation) {
            return back()->with('error', 'An active invitation already exists for this email.');
        }

        $token = Str::random(64);

        $invitation = StaffInvitation::create([
            'company_id' => $company->id,
            'invited_by' => $user->id,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'branch_id' => $validated['branch_id'] ?? null,
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        $acceptUrl = route('staff-invitations.show', ['token' => $token]);

        try {
            Mail::raw(
                "You have been invited to join {$company->name}.\n\nAccept invitation: {$acceptUrl}\n\nThis link expires in 7 days.",
                function ($message) use ($validated, $company) {
                    $message->to($validated['email'])
                        ->subject("Invitation to join {$company->name}");
                }
            );
        } catch (\Throwable $exception) {
            return back()->with('warning', 'Invitation created, but email could not be sent. Share this link manually: ' . $acceptUrl);
        }

        return back()->with('success', 'Invitation sent successfully.');
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

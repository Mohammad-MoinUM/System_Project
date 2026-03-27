<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyBranchController extends Controller
{
    /**
     * Show all branches for a company
     */
    public function index($companyId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            !in_array($user->getRoleInCompany($companyId), ['admin', 'manager'])) {
            abort(403);
        }

        $branches = $company->branches()
            ->with('branchManager')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('corporate.branches.index', [
            'company' => $company,
            'branches' => $branches,
        ]);
    }

    /**
     * Show form to create new branch
     */
    public function create($companyId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            $user->getRoleInCompany($companyId) !== 'admin') {
            abort(403);
        }

        // Get all staff members for manager dropdown
        $staff = $company->staff()
            ->with('user')
            ->where('is_active', true)
            ->get()
            ->map(function ($member) {
                return (object) [
                    'id' => $member->user_id,
                    'name' => $member->user->first_name . ' ' . $member->user->last_name,
                    'role' => $member->role,
                ];
            });

        return view('corporate.branches.create', [
            'company' => $company,
            'staff' => $staff,
        ]);
    }

    /**
     * Store new branch
     */
    public function store(Request $request, $companyId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            $user->getRoleInCompany($companyId) !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'branch_name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'branch_manager_name' => 'nullable|string|max:255',
        ]);

        $branch = $company->branches()->create($validated);

        return redirect()
            ->route('corporate.branches.index', $company->id)
            ->with('success', 'Branch created successfully.');
    }

    /**
     * Show branch details
     */
    public function show($companyId, $branchId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId)) {
            abort(403);
        }

        $branch = $company->branches()->findOrFail($branchId);
        $branch->load('staff', 'branchManager');

        $stats = [
            'total_staff' => $branch->staff()->where('is_active', true)->count(),
            'pending_requests' => $branch->serviceRequests()->where('status', 'pending')->count(),
            'completed_bookings' => $branch->bookings()
                ->where('is_corporate', true)
                ->where('status', 'completed')
                ->count(),
        ];

        return view('corporate.branches.show', [
            'company' => $company,
            'branch' => $branch,
            'stats' => $stats,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit($companyId, $branchId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            $user->getRoleInCompany($companyId) !== 'admin') {
            abort(403);
        }

        $branch = $company->branches()->findOrFail($branchId);

        return view('corporate.branches.edit', [
            'company' => $company,
            'branch' => $branch,
        ]);
    }

    /**
     * Update branch
     */
    public function update(Request $request, $companyId, $branchId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            $user->getRoleInCompany($companyId) !== 'admin') {
            abort(403);
        }

        $branch = $company->branches()->findOrFail($branchId);

        $validated = $request->validate([
            'branch_name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'branch_manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $branch->update($validated);

        return redirect()
            ->route('corporate.branches.show', [$company->id, $branch->id])
            ->with('success', 'Branch updated successfully.');
    }

    /**
     * Delete branch
     */
    public function destroy($companyId, $branchId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            $user->getRoleInCompany($companyId) !== 'admin') {
            abort(403);
        }

        $branch = $company->branches()->findOrFail($branchId);
        $branch->update(['is_active' => false]);

        return redirect()
            ->route('corporate.branches.index', $company->id)
            ->with('success', 'Branch deactivated successfully.');
    }
}

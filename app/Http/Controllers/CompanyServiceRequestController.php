<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyBranch;
use App\Models\CompanyServiceRequest;
use App\Models\Service;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyServiceRequestController extends Controller
{
    /**
     * Show all service requests for company
     */
    public function index($companyId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId)) {
            abort(403);
        }

        $requests = $company->serviceRequests()
            ->with('service', 'branch', 'requester', 'approver')
            ->when(request('status'), function ($q) {
                return $q->where('status', request('status'));
            })
            ->when(request('branch_id'), function ($q) {
                return $q->where('branch_id', request('branch_id'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $branches = $company->branches()->where('is_active', true)->get();

        return view('corporate.requests.index', [
            'company' => $company,
            'requests' => $requests,
            'branches' => $branches,
        ]);
    }

    /**
     * Show form to create service request
     */
    public function create($companyId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            !$user->canRequestInCompany($companyId)) {
            abort(403);
        }

        $branches = $company->branches()->where('is_active', true)->get();
        $services = Service::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        return view('corporate.requests.create', [
            'company' => $company,
            'branches' => $branches,
            'services' => $services,
        ]);
    }

    /**
     * Store new service request
     */
    public function store(Request $request, $companyId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            !$user->canRequestInCompany($companyId)) {
            abort(403);
        }

        $validated = $request->validate([
            'branch_id' => 'required|exists:company_branches,id',
            'service_id' => 'required|exists:services,id',
            'requested_date' => 'required|date|after:today',
            'requested_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        // Verify branch belongs to company
        $branch = $company->branches()->findOrFail($validated['branch_id']);

        // Get service price for estimate
        $service = Service::findOrFail($validated['service_id']);

        $serviceRequest = $company->serviceRequests()->create([
            'branch_id' => $validated['branch_id'],
            'service_id' => $validated['service_id'],
            'requested_by' => $user->id,
            'requested_date' => $validated['requested_date'],
            'requested_time' => $validated['requested_time'],
            'notes' => $validated['notes'],
            'status' => 'pending',
            'estimated_cost' => $service->price ?? 0,
        ]);

        return redirect()
            ->route('corporate.requests.show', [$company->id, $serviceRequest->id])
            ->with('success', 'Service request created. Awaiting approval.');
    }

    /**
     * Show request details
     */
    public function show($companyId, $requestId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId)) {
            abort(403);
        }

        $serviceRequest = $company->serviceRequests()
            ->with('service', 'branch', 'requester', 'approver')
            ->findOrFail($requestId);

        // Get related booking if exists
        $booking = $serviceRequest->status === 'approved' 
            ? Booking::where([
                'company_id' => $company->id,
                'branch_id' => $serviceRequest->branch_id,
                'service_id' => $serviceRequest->service_id,
                'requested_by' => $serviceRequest->requested_by,
                'is_corporate' => true,
            ])->first()
            : null;

        return view('corporate.requests.show', [
            'company' => $company,
            'request' => $serviceRequest,
            'booking' => $booking,
        ]);
    }

    /**
     * Show approval form (for approvers only)
     */
    public function approvalForm($companyId, $requestId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->canApproveInCompany($companyId)) {
            abort(403);
        }

        $serviceRequest = $company->serviceRequests()
            ->where('status', 'pending')
            ->with('service', 'branch', 'requester')
            ->findOrFail($requestId);

        $providers = \App\Models\User::where('role', 'provider')
            ->where('verification_status', 'approved')
            ->orderBy('name', 'asc')
            ->get();

        return view('corporate.requests.approve', [
            'company' => $company,
            'request' => $serviceRequest,
            'providers' => $providers,
        ]);
    }

    /**
     * Approve service request
     */
    public function approve(Request $request, $companyId, $requestId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->canApproveInCompany($companyId)) {
            abort(403);
        }

        $serviceRequest = $company->serviceRequests()
            ->where('status', 'pending')
            ->findOrFail($requestId);

        $validated = $request->validate([
            'provider_id' => 'required|exists:users,id',
        ]);

        $provider = \App\Models\User::where('role', 'provider')
            ->where('verification_status', 'approved')
            ->findOrFail($validated['provider_id']);

        // Approve the request
        $serviceRequest->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Create booking automatically
        $booking = Booking::create([
            'service_id' => $serviceRequest->service_id,
            'taker_id' => $company->primary_admin_id,
            'provider_id' => $provider->id,
            'company_id' => $company->id,
            'branch_id' => $serviceRequest->branch_id,
            'requested_by' => $serviceRequest->requested_by,
            'approved_by' => $user->id,
            'is_corporate' => true,
            'booking_date' => $serviceRequest->requested_date,
            'time_from' => $serviceRequest->requested_time,
            'status' => 'pending',
            'notes' => $serviceRequest->notes,
            'total' => $serviceRequest->estimated_cost,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('corporate.requests.show', [$company->id, $serviceRequest->id])
            ->with('success', 'Request approved and booking created.');
    }

    /**
     * Reject service request
     */
    public function reject(Request $request, $companyId, $requestId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->canApproveInCompany($companyId)) {
            abort(403);
        }

        $serviceRequest = $company->serviceRequests()
            ->where('status', 'pending')
            ->findOrFail($requestId);

        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $serviceRequest->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()
            ->route('corporate.requests.index', $company->id)
            ->with('success', 'Request rejected successfully.');
    }
}

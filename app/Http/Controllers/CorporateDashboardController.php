<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Booking;
use App\Models\CompanyServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CorporateDashboardController extends Controller
{
    /**
     * Show corporate dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Get all companies where user is admin or staff
        $companies = $user->companyMemberships()
            ->with('company')
            ->where('is_active', true)
            ->get()
            ->pluck('company')
            ->unique()
            ->values();

        if ($companies->isEmpty()) {
            return view('corporate.no-company');
        }

        // Honor selected company from session if still accessible.
        $activeCompanyId = (int) session('active_company_id', 0);
        $company = $companies->firstWhere('id', $activeCompanyId) ?? $companies->first();

        // Keep session in sync with effective company selection.
        session(['active_company_id' => $company->id]);

        // Get dashboard statistics
        $stats = [
            'total_branches' => $company->branches()->where('is_active', true)->count(),
            'total_staff' => $company->staff()->where('is_active', true)->count(),
            'pending_requests' => $company->serviceRequests()->where('status', 'pending')->count(),
            'approved_requests' => $company->serviceRequests()->where('status', 'approved')->count(),
            'completed_bookings' => $company->bookings()
                ->where('is_corporate', true)
                ->where('status', 'completed')
                ->count(),
            'in_progress_bookings' => $company->bookings()
                ->where('is_corporate', true)
                ->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->count(),
        ];

        // Get recent bookings
        $recentBookings = $company->bookings()
            ->where('is_corporate', true)
            ->with('service', 'provider', 'branch')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get pending approvals
        $pendingApprovals = $company->serviceRequests()
            ->where('status', 'pending')
            ->with('requester', 'service', 'branch')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get current month stats
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $monthlySpend = $company->bookings()
            ->where('is_corporate', true)
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->sum('total');

        $userRole = $user->getRoleInCompany($company->id);

        return view('corporate.dashboard', [
            'company' => $company,
            'companies' => $companies,
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'pendingApprovals' => $pendingApprovals,
            'monthlySpend' => $monthlySpend,
            'userRole' => $userRole,
        ]);
    }

    /**
     * Switch to different company
     */
    public function switchCompany($companyId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId)) {
            return redirect()->route('corporate.dashboard')
                ->with('error', 'You do not have access to this company.');
        }

        session(['active_company_id' => $companyId]);
        return redirect()->route('corporate.dashboard');
    }

    /**
     * Show company bookings history
     */
    public function bookingHistory($companyId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId)) {
            abort(403);
        }

        $bookings = $company->bookings()
            ->where('is_corporate', true)
            ->with('service', 'provider', 'branch', 'taker')
            ->filterByStatus(request('status'))
            ->filterByBranch(request('branch_id'))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('corporate.booking-history', [
            'company' => $company,
            'bookings' => $bookings,
        ]);
    }

    /**
     * Show booking details
     */
    public function bookingDetails($companyId, $bookingId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId)) {
            abort(403);
        }

        $booking = $company->bookings()->findOrFail($bookingId);
        $booking->load('service', 'provider', 'branch', 'taker', 'reviews');

        return view('corporate.booking-details', [
            'company' => $company,
            'booking' => $booking,
        ]);
    }
}

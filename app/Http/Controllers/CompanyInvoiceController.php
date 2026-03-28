<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyInvoiceController extends Controller
{
    /**
     * Show invoices for company
     */
    public function index($companyId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId)) {
            abort(403);
        }

        // Only finance and admin can view invoices
        if (!in_array($user->getRoleInCompany($companyId), ['admin', 'finance'])) {
            abort(403);
        }

        $invoices = $company->invoices()
            ->when(request('status'), function ($q) {
                return $q->where('status', request('status'));
            })
            ->when(request('year'), function ($q) {
                return $q->where('year', request('year'));
            })
            ->when(request('month'), function ($q) {
                return $q->where('month', request('month'));
            })
            ->orderBy('invoice_date', 'desc')
            ->paginate(15);

        $years = $company->invoices()
            ->selectRaw('DISTINCT year')
            ->pluck('year')
            ->sort()
            ->reverse();

        return view('corporate.invoices.index', [
            'company' => $company,
            'invoices' => $invoices,
            'years' => $years,
        ]);
    }

    /**
     * Show invoice details
     */
    public function show($companyId, $invoiceId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId)) {
            abort(403);
        }

        if (!in_array($user->getRoleInCompany($companyId), ['admin', 'finance'])) {
            abort(403);
        }

        $invoice = $company->invoices()->findOrFail($invoiceId);

        // Get bookings for this invoice period
        $bookings = $company->bookings()
            ->where('is_corporate', true)
            ->where('status', 'completed')
            ->whereYear('completed_at', $invoice->year)
            ->whereMonth('completed_at', $invoice->month)
            ->with('service', 'provider', 'branch')
            ->get();

        return view('corporate.invoices.show', [
            'company' => $company,
            'invoice' => $invoice,
            'bookings' => $bookings,
        ]);
    }

    /**
     * Generate monthly invoice (admin only)
     */
    public function generateMonthly($companyId, $month, $year)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId) || 
            $user->getRoleInCompany($companyId) !== 'admin') {
            abort(403);
        }

        // Check if invoice already exists
        $existing = $company->invoices()
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existing) {
            return redirect()
                ->route('corporate.invoices.show', [$company->id, $existing->id])
                ->with('info', 'Invoice already generated for this period.');
        }

        // Get bookings for the month
        $bookings = $company->bookings()
            ->where('is_corporate', true)
            ->where('status', 'completed')
            ->whereYear('completed_at', $year)
            ->whereMonth('completed_at', $month)
            ->get();

        if ($bookings->isEmpty()) {
            return back()->with('error', 'No completed bookings for this period.');
        }

        $subtotal = $bookings->sum('total');
        $tax = $subtotal * 0.05; // 5% tax
        $total = $subtotal + $tax;

        $invoice = $company->invoices()->create([
            'invoice_number' => $this->generateInvoiceNumber($company, $month, $year),
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'status' => 'draft',
            'month' => $month,
            'year' => $year,
            'notes' => 'Monthly service invoice for all branches',
        ]);

        return redirect()
            ->route('corporate.invoices.show', [$company->id, $invoice->id])
            ->with('success', 'Invoice generated successfully.');
    }

    /**
     * Download invoice as PDF
     */
    public function download($companyId, $invoiceId)
    {
        $user = Auth::user();
        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId)) {
            abort(403);
        }

        if (!in_array($user->getRoleInCompany($companyId), ['admin', 'finance'])) {
            abort(403);
        }

        $invoice = $company->invoices()->findOrFail($invoiceId);

        // Generate PDF (basic implementation, use PDF library in production)
        return response()->download(storage_path("invoices/{$invoice->invoice_number}.pdf"));
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber($company, $month, $year): string
    {
        $count = $company->invoices()
            ->where('year', $year)
            ->where('month', $month)
            ->count() + 1;

        return sprintf(
            'INV-%d-%04d-%02d-%03d',
            $company->id,
            $year,
            $month,
            $count
        );
    }
}

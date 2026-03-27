@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-base-content mb-2">Invoices</h1>
                <p class="text-base-content/70">View and download your invoices</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-6">{{ session('success') }}</div>
        @endif

        @if($invoices->count() > 0)
        <div class="card bg-base-100 shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Period</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->billing_period_start->format('M Y') }}</td>
                            <td class="font-semibold">${{ number_format($invoice->total_amount, 2) }}</td>
                            <td>
                                <div class="badge {{ 
                                    $invoice->status === 'paid' ? 'badge-success' : 
                                    ($invoice->status === 'overdue' ? 'badge-error' : 'badge-warning')
                                }}">
                                    {{ ucfirst($invoice->status) }}
                                </div>
                            </td>
                            <td>{{ $invoice->due_date->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('corporate.invoices.show', [$company->id, $invoice->id]) }}" class="btn btn-sm btn-outline">
                                    View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body text-center">
                <p class="text-base-content/70">No invoices yet. Invoices will be generated after your first service booking.</p>
            </div>
        </div>
        @endif

        <div class="mt-8">
            <a href="{{ route('corporate.dashboard') }}" class="btn btn-outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection

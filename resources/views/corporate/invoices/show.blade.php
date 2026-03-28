@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-start mb-8">
            <div>
                <h1 class="text-3xl font-bold text-base-content mb-2">Invoice #{{ $invoice->invoice_number }}</h1>
                <p class="text-base-content/70">{{ $invoice->billing_period_start->format('F Y') }}</p>
            </div>
            <div class="badge badge-lg {{ 
                $invoice->status === 'paid' ? 'badge-success' : 
                ($invoice->status === 'overdue' ? 'badge-error' : 'badge-warning')
            }}">
                {{ ucfirst($invoice->status) }}
            </div>
        </div>

        <!-- Invoice Content -->
        <div class="card bg-base-100 shadow-lg mb-8">
            <div class="card-body">
                <!-- Company Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 class="font-semibold text-base-content mb-2">From:</h3>
                        <p class="font-bold">{{ config('app.name', 'HaalChaal') }}</p>
                        <p class="text-sm text-base-content/70">Service Provider</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-base-content mb-2">Bill To:</h3>
                        <p class="font-bold">{{ $company->company_name }}</p>
                        <p class="text-sm text-base-content/70">{{ $company->address }}</p>
                        <p class="text-sm text-base-content/70">{{ $company->city }}</p>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Invoice Details -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div>
                        <p class="text-sm text-base-content/70">Invoice Date</p>
                        <p class="font-semibold">{{ $invoice->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-base-content/70">Due Date</p>
                        <p class="font-semibold">{{ $invoice->due_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-base-content/70">Billing Period</p>
                        <p class="font-semibold">{{ $invoice->billing_period_start->format('M d') }} - {{ $invoice->billing_period_end->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-base-content/70">Reference</p>
                        <p class="font-semibold">{{ $invoice->invoice_number }}</p>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Line Items -->
                <div class="overflow-x-auto mb-8">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-right">Quantity</th>
                                <th class="text-right">Rate</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->lineItems ?? [] as $item)
                            <tr>
                                <td>{{ $item->description ?? 'Service' }}</td>
                                <td class="text-right">{{ $item->quantity ?? 1 }}</td>
                                <td class="text-right">${{ number_format($item->rate ?? 0, 2) }}</td>
                                <td class="text-right font-semibold">${{ number_format(($item->quantity ?? 1) * ($item->rate ?? 0), 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-base-content/70 py-4">No line items available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="flex justify-end mb-8">
                    <div class="w-full md:w-1/3">
                        <div class="flex justify-between py-2 border-b">
                            <span>Subtotal:</span>
                            <span>${{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 text-lg font-bold">
                            <span>Total:</span>
                            <span>${{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                @if($invoice->notes)
                <div class="divider"></div>
                <div>
                    <p class="text-sm text-base-content/70 mb-2">Notes:</p>
                    <p class="text-base-content/70">{{ $invoice->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 mb-8">
            <a href="#" class="btn btn-outline" onclick="window.print()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print
            </a>
            <a href="{{ route('corporate.invoices.index', $company->id) }}" class="btn btn-outline flex-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Invoices
            </a>
        </div>
    </div>
</div>
@endsection

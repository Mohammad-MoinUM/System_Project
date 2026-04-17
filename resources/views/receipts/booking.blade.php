<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $receiptNumber }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        .header { margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; }
        .muted { color: #6b7280; }
        .box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 0; vertical-align: top; }
        .label { color: #6b7280; width: 40%; }
        .right { text-align: right; }
        .small { font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Payment Receipt</div>
        <div class="muted">Receipt #{{ $receiptNumber }}</div>
    </div>

    <div class="box">
        <table>
            <tr><td class="label">Booking ID</td><td>#{{ $booking->id }}</td></tr>
            <tr><td class="label">Customer</td><td>{{ $booking->taker->name }}</td></tr>
            <tr><td class="label">Provider</td><td>{{ $booking->provider->name }}</td></tr>
            <tr><td class="label">Service</td><td>{{ $booking->service->name }}</td></tr>
            <tr><td class="label">Payment Method</td><td>{{ ucfirst($booking->payment_method ?? 'n/a') }}</td></tr>
            <tr><td class="label">Payment Status</td><td>{{ ucfirst(str_replace('_', ' ', $booking->payment_status ?? 'unpaid')) }}</td></tr>
            <tr><td class="label">Paid At</td><td>{{ $booking->paid_at ? $booking->paid_at->format('M d, Y g:i A') : 'N/A' }}</td></tr>
        </table>
    </div>

    <div class="box">
        <table>
            <tr><td class="label">Subtotal</td><td class="right">{{ number_format((float) $booking->total, 2) }} BDT</td></tr>
            <tr><td class="label">Upfront Paid</td><td class="right">{{ number_format((float) $booking->upfront_amount, 2) }} BDT</td></tr>
            <tr><td class="label">Remaining</td><td class="right">{{ number_format((float) $booking->remaining_amount, 2) }} BDT</td></tr>
            <tr><td class="label">Cashback</td><td class="right">{{ number_format((float) $booking->cashback_amount, 2) }} BDT</td></tr>
        </table>
    </div>

    <div class="box small">
        <div class="muted">Payments</div>
        @forelse($payments as $payment)
            <p>{{ ucfirst($payment->method) }} - {{ number_format((float) $payment->amount, 2) }} BDT - {{ $payment->captured_at ? $payment->captured_at->format('M d, Y g:i A') : 'N/A' }}</p>
        @empty
            <p>No recorded payments.</p>
        @endforelse
    </div>
</body>
</html>
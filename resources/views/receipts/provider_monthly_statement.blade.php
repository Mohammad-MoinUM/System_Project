<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Provider Monthly Invoice</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        h1 { margin: 0 0 6px; font-size: 22px; }
        h2 { margin: 0 0 10px; font-size: 14px; color: #4b5563; }
        .muted { color: #6b7280; }
        .summary { margin: 18px 0; padding: 12px; border: 1px solid #e5e7eb; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
        .footer { margin-top: 18px; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Provider Monthly Invoice</h1>
    <h2>{{ $monthLabel }}</h2>

    <p><strong>Provider:</strong> {{ $provider->name }} ({{ $provider->email }})</p>

    <div class="summary">
        <p><strong>Total Completed Jobs:</strong> {{ $totalJobs }}</p>
        <p><strong>Total Earnings (Gross):</strong> BDT {{ number_format($totalAmount, 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Service</th>
                <th>Category</th>
                <th>Customer</th>
                <th class="right">Amount (BDT)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $index => $booking)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ optional($booking->updated_at)->format('Y-m-d') }}</td>
                    <td>{{ optional($booking->service)->name ?: 'Service' }}</td>
                    <td>{{ optional($booking->service)->category ?: 'N/A' }}</td>
                    <td>{{ optional($booking->taker)->name ?: 'Customer' }}</td>
                    <td class="right">{{ number_format((float) $booking->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="muted">No completed bookings found for this month.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ $generatedAt->format('Y-m-d H:i') }}
    </div>
</body>
</html>

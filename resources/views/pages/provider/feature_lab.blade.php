{{-- ═══════════════════ Feature Lab (New) ═══════════════════ --}}
<section class="bg-base-100">
  <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-base-content">Feature Lab</h2>
    <p class="mt-2 text-base-content/60">Experimental growth features for retention, pricing, and daily action planning.</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
      <div class="rounded-2xl border border-base-300 bg-base-100 p-5 lg:col-span-2">
        <h3 class="text-lg font-bold text-base-content">Repeat Client Risk Radar</h3>
        <p class="mt-1 text-sm text-base-content/60">Customers who used your service before but have been inactive recently.</p>

        <div class="mt-4 overflow-x-auto">
          <table class="table w-full">
            <thead>
              <tr>
                <th>Customer</th>
                <th>Completed Jobs</th>
                <th>Inactive (Days)</th>
                <th>Avg Spend</th>
                <th>Risk</th>
              </tr>
            </thead>
            <tbody>
              @forelse(($retentionOpportunities ?? collect()) as $item)
                <tr>
                  <td class="font-semibold">{{ $item['customer_name'] }}</td>
                  <td>{{ $item['completed_jobs'] }}</td>
                  <td>{{ $item['days_inactive'] }}</td>
                  <td>{{ $currencySymbol }} {{ number_format(($item['avg_total'] ?? 0) * $currencyRate, 2) }}</td>
                  <td>
                    @php
                      $riskBadge = $item['risk'] === 'high' ? 'badge-error' : ($item['risk'] === 'medium' ? 'badge-warning' : 'badge-info');
                    @endphp
                    <span class="badge {{ $riskBadge }}">{{ ucfirst($item['risk']) }}</span>
                  </td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-base-content/50">No repeat client risks detected yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
        <h3 class="text-lg font-bold text-base-content">Next Best Actions</h3>
        <div class="mt-4 space-y-3">
          @foreach(($nextBestActions ?? collect()) as $action)
            @php
              $badgeClass = $action['priority'] === 'high' ? 'badge-error' : ($action['priority'] === 'medium' ? 'badge-warning' : 'badge-success');
            @endphp
            <div class="rounded-xl bg-base-200 p-4">
              <div class="flex items-center justify-between gap-2">
                <p class="font-semibold text-base-content">{{ $action['title'] }}</p>
                <span class="badge badge-xs {{ $badgeClass }}">{{ strtoupper($action['priority']) }}</span>
              </div>
              <p class="mt-2 text-sm text-base-content/70">{{ $action['description'] }}</p>
              <a href="{{ $action['route'] }}" class="btn btn-primary btn-xs mt-3">{{ $action['cta'] }}</a>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="mt-6 rounded-2xl border border-base-300 bg-base-100 p-5">
      <h3 class="text-lg font-bold text-base-content">Price Competitiveness Meter</h3>
      <p class="mt-1 text-sm text-base-content/60">Compare your service prices with the current market average in each category.</p>

      <div class="mt-4 overflow-x-auto">
        <table class="table w-full">
          <thead>
            <tr>
              <th>Service</th>
              <th>Category</th>
              <th>Your Price</th>
              <th>Market Avg</th>
              <th>Difference</th>
              <th>Signal</th>
            </tr>
          </thead>
          <tbody>
            @forelse(($pricingInsights ?? collect()) as $insight)
              <tr>
                <td class="font-semibold">{{ $insight['service_name'] }}</td>
                <td>{{ $insight['category'] }}</td>
                <td>{{ $currencySymbol }} {{ number_format(($insight['price'] ?? 0) * $currencyRate, 2) }}</td>
                <td>{{ $currencySymbol }} {{ number_format(($insight['market_avg'] ?? 0) * $currencyRate, 2) }}</td>
                <td>
                  @if($insight['delta_percent'] !== null)
                    {{ $insight['delta_percent'] > 0 ? '+' : '' }}{{ $insight['delta_percent'] }}%
                  @else
                    N/A
                  @endif
                </td>
                <td>
                  @php
                    $signalBadge = $insight['signal'] === 'high' ? 'badge-warning' : ($insight['signal'] === 'low' ? 'badge-info' : 'badge-success');
                    $signalLabel = $insight['signal'] === 'high' ? 'Above Market' : ($insight['signal'] === 'low' ? 'Below Market' : 'Balanced');
                  @endphp
                  <span class="badge {{ $signalBadge }}">{{ $signalLabel }}</span>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-base-content/50">No active services found for pricing analysis.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

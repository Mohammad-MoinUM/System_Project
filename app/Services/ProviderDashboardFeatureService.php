<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProviderDashboardFeatureService
{
    /**
     * Build all additional dashboard features that are expensive/noisy in controller.
     */
    public function build(int $providerId, Carbon $now): array
    {
        return [
            'retentionOpportunities' => $this->buildRetentionOpportunities($providerId, $now),
            'pricingInsights' => $this->buildPricingInsights($providerId),
            'nextBestActions' => $this->buildNextBestActions($providerId, $now),
        ];
    }

    /**
     * Find customers likely to rebook based on past frequency but recent inactivity.
     */
    private function buildRetentionOpportunities(int $providerId, Carbon $now): Collection
    {
        $rows = Booking::query()
            ->where('provider_id', $providerId)
            ->where('status', 'completed')
            ->select(
                'taker_id',
                DB::raw('COUNT(*) as completed_jobs'),
                DB::raw('MAX(updated_at) as last_completed_at'),
                DB::raw('AVG(total) as avg_total')
            )
            ->groupBy('taker_id')
            ->havingRaw('COUNT(*) >= 2')
            ->orderByDesc(DB::raw('MAX(updated_at)'))
            ->limit(12)
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $names = DB::table('users')
            ->whereIn('id', $rows->pluck('taker_id')->all())
            ->pluck('name', 'id');

        return $rows
            ->map(function ($row) use ($names, $now) {
                $lastCompleted = Carbon::parse($row->last_completed_at);
                $daysInactive = (int) $lastCompleted->diffInDays($now);

                return [
                    'customer_name' => $names[(int) $row->taker_id] ?? 'Customer',
                    'completed_jobs' => (int) $row->completed_jobs,
                    'days_inactive' => $daysInactive,
                    'avg_total' => (float) $row->avg_total,
                    'risk' => $daysInactive >= 45 ? 'high' : ($daysInactive >= 25 ? 'medium' : 'low'),
                ];
            })
            ->filter(fn (array $item) => $item['days_inactive'] >= 20)
            ->sortByDesc('days_inactive')
            ->values()
            ->take(5);
    }

    /**
     * Compare provider prices vs category market average to guide pricing decisions.
     */
    private function buildPricingInsights(int $providerId): Collection
    {
        $providerServices = Service::query()
            ->where('provider_id', $providerId)
            ->where('is_active', true)
            ->select('id', 'name', 'category', 'price')
            ->get();

        if ($providerServices->isEmpty()) {
            return collect();
        }

        $categoryAverages = Service::query()
            ->where('is_active', true)
            ->whereIn('category', $providerServices->pluck('category')->unique()->all())
            ->groupBy('category')
            ->select('category', DB::raw('AVG(price) as avg_price'))
            ->get()
            ->keyBy('category');

        return $providerServices->map(function (Service $service) use ($categoryAverages) {
            $marketAvg = (float) ($categoryAverages[$service->category]->avg_price ?? 0);
            $price = (float) $service->price;

            $deltaPercent = $marketAvg > 0
                ? (int) round((($price - $marketAvg) / $marketAvg) * 100)
                : null;

            $signal = 'balanced';
            if ($deltaPercent !== null) {
                if ($deltaPercent >= 20) {
                    $signal = 'high';
                } elseif ($deltaPercent <= -20) {
                    $signal = 'low';
                }
            }

            return [
                'service_name' => $service->name,
                'category' => $service->category,
                'price' => $price,
                'market_avg' => $marketAvg,
                'delta_percent' => $deltaPercent,
                'signal' => $signal,
            ];
        })->values();
    }

    /**
     * Generate practical actions from current month/provider behavior.
     */
    private function buildNextBestActions(int $providerId, Carbon $now): Collection
    {
        $monthStart = $now->copy()->startOfMonth();

        $pendingCount = Booking::where('provider_id', $providerId)
            ->where('status', 'pending')
            ->count();

        $cancelRateBase = Booking::where('provider_id', $providerId)
            ->whereIn('status', ['completed', 'cancelled'])
            ->count();

        $cancelled = Booking::where('provider_id', $providerId)
            ->where('status', 'cancelled')
            ->count();

        $cancelRate = $cancelRateBase > 0
            ? (int) round(($cancelled / $cancelRateBase) * 100)
            : 0;

        $monthCompleted = Booking::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->where('updated_at', '>=', $monthStart)
            ->count();

        $actions = collect();

        if ($pendingCount > 0) {
            $actions->push([
                'title' => 'Clear pending requests quickly',
                'description' => "You have {$pendingCount} pending booking request(s). Accept or reject quickly to improve response visibility.",
                'priority' => 'high',
                'route' => route('provider.jobs'),
                'cta' => 'Open Jobs',
            ]);
        }

        if ($cancelRate >= 20) {
            $actions->push([
                'title' => 'Reduce cancellations',
                'description' => "Your cancellation rate is {$cancelRate}%. Confirm schedule details before accepting new jobs.",
                'priority' => 'high',
                'route' => route('provider.schedule'),
                'cta' => 'Fix Schedule',
            ]);
        }

        if ($monthCompleted < 10) {
            $actions->push([
                'title' => 'Increase monthly completed jobs',
                'description' => "Only {$monthCompleted} completed job(s) this month. Expand service areas and enable more service slots.",
                'priority' => 'medium',
                'route' => route('provider.service-areas.index'),
                'cta' => 'Expand Areas',
            ]);
        }

        if ($actions->isEmpty()) {
            $actions->push([
                'title' => 'Momentum is strong',
                'description' => 'Performance looks healthy. Keep consistent availability in your top demand hours.',
                'priority' => 'low',
                'route' => route('provider.schedule'),
                'cta' => 'View Schedule',
            ]);
        }

        return $actions->take(4)->values();
    }
}

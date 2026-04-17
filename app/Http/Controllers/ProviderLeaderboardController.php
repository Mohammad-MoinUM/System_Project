<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class ProviderLeaderboardController extends Controller
{
    public function index(): View
    {
        $providers = User::query()
            ->where('role', 'provider')
            ->where('verification_status', 'approved')
            ->withCount([
                'bookingsAsProvider as completed_jobs' => fn ($q) => $q->where('status', 'completed'),
            ])
            ->withAvg('reviewsReceived as avg_rating', 'rating')
            ->orderByDesc('completed_jobs')
            ->orderByDesc('avg_rating')
            ->limit(50)
            ->get();

        $ranked = $providers->values()->map(function (User $provider, int $index) {
            $rank = $index + 1;

            $badge = 'Pro';
            if ($rank === 1) {
                $badge = 'Elite #1';
            } elseif ($rank <= 3) {
                $badge = 'Top Performer';
            } elseif ($rank <= 10) {
                $badge = 'Rising Star';
            }

            return [
                'rank' => $rank,
                'badge' => $badge,
                'name' => $provider->name,
                'city' => $provider->city,
                'area' => $provider->area,
                'completed_jobs' => (int) $provider->completed_jobs,
                'avg_rating' => $provider->avg_rating ? round((float) $provider->avg_rating, 2) : null,
            ];
        });

        return view('pages.provider.leaderboard', [
            'rankedProviders' => $ranked,
        ]);
    }
}

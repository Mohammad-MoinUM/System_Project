<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProviderLeaderboardController extends Controller
{
    public function index(): View
    {
        $topProviders = Booking::query()
            ->select('provider_id')
            ->selectRaw('COUNT(*) as completed_jobs')
            ->selectRaw('SUM(total) as gross_earnings')
            ->selectRaw('AVG(CASE WHEN total > 0 THEN total ELSE NULL END) as average_job_value')
            ->where('status', 'completed')
            ->whereNotNull('provider_id')
            ->groupBy('provider_id')
            ->orderByDesc('completed_jobs')
            ->with('provider:id,first_name,last_name,photo,verification_status,skill_verification_status')
            ->limit(25)
            ->get();

        $monthlyWinners = Booking::query()
            ->select('provider_id', DB::raw('COUNT(*) as completed_jobs'))
            ->where('status', 'completed')
            ->whereYear('updated_at', now()->year)
            ->whereMonth('updated_at', now()->month)
            ->groupBy('provider_id')
            ->orderByDesc('completed_jobs')
            ->with('provider:id,first_name,last_name,photo')
            ->limit(5)
            ->get();

        return view('pages.provider_leaderboard', compact('topProviders', 'monthlyWinners'));
    }
}

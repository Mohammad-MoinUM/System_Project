<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Review;
use Illuminate\View\View;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Show admin dashboard with statistics
     */
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_providers' => User::where('role', 'provider')->count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'completed_bookings' => Booking::where('status', 'completed')->count(),
            'total_services' => Service::count(),
            'active_services' => Service::where('is_active', true)->count(),
            'total_reviews' => Review::count(),
            'avg_rating' => Review::avg('rating') ?? 0,
        ];

        $recent_bookings = Booking::with('taker', 'provider', 'service')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recent_users = User::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recent_bookings' => $recent_bookings,
            'recent_users' => $recent_users,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display user notifications
     */
    public function index(): View
    {
        $user = Auth::user();
        $unreadCount = $user->unreadNotifications()->count();

        $notifications = $user
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('pages.notifications', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark a single notification as read
     */
    public function markAsRead(string $id): RedirectResponse
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('price')->get();
        $activeSubscription = UserSubscription::where('user_id', Auth::id())
            ->where('status', 'active')
            ->whereDate('ends_on', '>=', now()->toDateString())
            ->latest('id')
            ->first();

        return view('pages.subscriptions', compact('plans', 'activeSubscription'));
    }

    public function subscribe(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        if (!$plan->is_active) {
            return back()->with('error', 'This subscription plan is not available now.');
        }

        $validated = $request->validate([
            'months' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $months = (int) ($validated['months'] ?? 1);

        UserSubscription::where('user_id', Auth::id())
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        UserSubscription::create([
            'user_id' => Auth::id(),
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->addMonthsNoOverflow($months)->toDateString(),
            'used_services_count' => 0,
        ]);

        return back()->with('success', 'Subscription activated successfully.');
    }

    public function cancel(): RedirectResponse
    {
        UserSubscription::where('user_id', Auth::id())
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        return back()->with('success', 'Subscription cancelled.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->dashboard();
        }

        return view('pages.getStarted');
    }

    public function services(): View
    {
        $categories = Service::where('is_active', true)
            ->select('category', DB::raw('COUNT(*) as services_count'))
            ->groupBy('category')
            ->orderByDesc('services_count')
            ->get();

        return view('pages.services', compact('categories'));
    }

    public function howItWorks(): View
    {
        return view('pages.how_it_works');
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function contactStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        Log::info('Contact form submission', $validated);

        return back()->with('success', 'Thank you for your message! We will get back to you soon.');
    }

    public function dashboard(): RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        return $user->role === 'provider'
            ? redirect()->route('provider.dashboard')
            : redirect()->route('customer.dashboard');
    }

    public function settings(): View
    {
        return view('pages.settings');
    }
}

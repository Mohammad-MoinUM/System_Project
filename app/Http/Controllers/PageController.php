<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.getStarted');
    }

    public function services(): RedirectResponse
    {
        return redirect()->route('home');
    }

    public function howItWorks(): RedirectResponse
    {
        return redirect()->route('home');
    }

    public function about(): RedirectResponse
    {
        return redirect()->route('home');
    }

    public function contact(): RedirectResponse
    {
        return redirect()->route('home');
    }

    public function dashboard(): RedirectResponse
    {
        return redirect()->route('home');
    }

    public function profile(): RedirectResponse
    {
        return redirect()->route('home');
    }

    public function settings(): RedirectResponse
    {
        return redirect()->route('home');
    }
}

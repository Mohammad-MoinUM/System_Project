<?php

namespace App\Http\Controllers;

use App\Models\ProviderPortfolioItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProviderPortfolioController extends Controller
{
    public function index(): View
    {
        $items = ProviderPortfolioItem::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('pages.provider.portfolio', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'job_date' => ['nullable', 'date'],
            'is_public' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'before_image' => ['nullable', 'image', 'max:5120'],
            'after_image' => ['nullable', 'image', 'max:5120'],
        ]);

        if (!$request->hasFile('cover_image') && !$request->hasFile('before_image') && !$request->hasFile('after_image')) {
            return back()->with('error', 'Upload at least one image for your portfolio item.');
        }

        ProviderPortfolioItem::create([
            'user_id' => Auth::id(),
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'job_date' => $data['job_date'] ?? null,
            'is_public' => (bool) ($data['is_public'] ?? true),
            'cover_image_path' => $request->file('cover_image')?->store('portfolio', 'public'),
            'before_image_path' => $request->file('before_image')?->store('portfolio', 'public'),
            'after_image_path' => $request->file('after_image')?->store('portfolio', 'public'),
        ]);

        return back()->with('success', 'Portfolio item added.');
    }

    public function destroy(ProviderPortfolioItem $portfolio): RedirectResponse
    {
        if ($portfolio->user_id !== Auth::id()) {
            abort(403);
        }

        foreach (['cover_image_path', 'before_image_path', 'after_image_path'] as $field) {
            if (!empty($portfolio->{$field})) {
                Storage::disk('public')->delete($portfolio->{$field});
            }
        }

        $portfolio->delete();

        return back()->with('success', 'Portfolio item removed.');
    }
}

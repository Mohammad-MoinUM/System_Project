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
            ->paginate(12);

        return view('pages.provider.portfolio', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'job_date' => ['nullable', 'date'],
            'is_public' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'before_image' => ['nullable', 'image', 'max:4096'],
            'after_image' => ['nullable', 'image', 'max:4096'],
        ]);

        $itemData = [
            'user_id' => Auth::id(),
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'job_date' => $validated['job_date'] ?? null,
            'is_public' => $request->boolean('is_public', true),
        ];

        foreach (['cover_image' => 'cover_image_path', 'before_image' => 'before_image_path', 'after_image' => 'after_image_path'] as $fileField => $dbField) {
            if ($request->hasFile($fileField)) {
                $itemData[$dbField] = $request->file($fileField)->store('portfolio', 'public');
            }
        }

        ProviderPortfolioItem::create($itemData);

        return back()->with('success', 'Portfolio item added successfully.');
    }

    public function destroy(ProviderPortfolioItem $item): RedirectResponse
    {
        if ((int) $item->user_id !== (int) Auth::id()) {
            abort(403);
        }

        foreach (['cover_image_path', 'before_image_path', 'after_image_path'] as $pathField) {
            if (!empty($item->{$pathField})) {
                Storage::disk('public')->delete($item->{$pathField});
            }
        }

        $item->delete();

        return back()->with('success', 'Portfolio item removed.');
    }
}

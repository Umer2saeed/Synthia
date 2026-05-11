<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Series;
use App\Models\SeriesPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeriesController extends Controller
{
    public function index()
    {
        $series = Series::with('user')
            ->withCount('posts')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.series.index', compact('series'));
    }

    public function create()
    {
        return view('admin.series.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'slug'        => ['nullable', 'string', 'max:220', 'unique:series,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'is_complete' => ['boolean'],
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')
                ->store('series', 'public');
        }

        Series::create([
            'user_id'     => auth()->id(),
            'title'       => $validated['title'],
            'slug'        => $validated['slug'] ?? Series::generateUniqueSlug($validated['title']),
            'description' => $validated['description'] ?? null,
            'cover_image' => $coverPath,
            'is_complete' => $request->boolean('is_complete', false),
        ]);

        return redirect()
            ->route('admin.series.index')
            ->with('success', 'Series created successfully.');
    }

    public function edit(Series $series)
    {
        $series->load(['posts' => fn($q) => $q->orderByPivot('order')]);

        return view('admin.series.edit', compact('series'));
    }

    public function update(Request $request, Series $series): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'slug'        => ['nullable', 'string', 'max:220', 'unique:series,slug,' . $series->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'is_complete' => ['boolean'],
        ]);

        $coverPath = $series->cover_image;
        if ($request->hasFile('cover_image')) {
            if ($series->cover_image) {
                Storage::disk('public')->delete($series->cover_image);
            }
            $coverPath = $request->file('cover_image')->store('series', 'public');
        }

        $series->update([
            'title'       => $validated['title'],
            'slug'        => $validated['slug'] ?? Series::generateUniqueSlug($validated['title'], $series->id),
            'description' => $validated['description'] ?? null,
            'cover_image' => $coverPath,
            'is_complete' => $request->boolean('is_complete', false),
        ]);

        return redirect()
            ->route('admin.series.index')
            ->with('success', 'Series updated.');
    }

    public function destroy(Series $series): RedirectResponse
    {
        if ($series->cover_image) {
            Storage::disk('public')->delete($series->cover_image);
        }

        $series->delete();

        return redirect()
            ->route('admin.series.index')
            ->with('success', 'Series deleted.');
    }
}

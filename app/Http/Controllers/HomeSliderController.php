<?php

namespace App\Http\Controllers;

use App\Models\HomeSlider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Admin management of the home page hero slides (`home_sliders`).
 * When no slide is active the public site shows built-in default slides (see App\Support\HeroSlides).
 */
class HomeSliderController extends Controller
{
    public function index()
    {
        $sliders = HomeSlider::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.sliders.index', compact('sliders'));
    }

    public function create()
    {
        $nextOrder = ((int) HomeSlider::max('sort_order')) + 1;

        return view('admin.sliders.form', ['slider' => null, 'nextOrder' => $nextOrder]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['image_path'] = $request->hasFile('image')
            ? $request->file('image')->store('sliders', 'public')
            : ''; // column is NOT NULL; an empty path means "no image, show the hero video"

        HomeSlider::create($data);

        return redirect()->route('admin.sliders.index')->with('success', 'Hero slide added successfully.');
    }

    public function edit($id)
    {
        $slider = HomeSlider::findOrFail($id);

        return view('admin.sliders.form', ['slider' => $slider, 'nextOrder' => $slider->sort_order]);
    }

    public function update(Request $request, $id)
    {
        $slider = HomeSlider::findOrFail($id);
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('sliders', 'public');
            $this->deleteImage($slider->image_path);
            $data['image_path'] = $newPath;
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($slider->image_path);
            $data['image_path'] = '';
        }

        $slider->update($data);

        return redirect()->route('admin.sliders.index')->with('success', 'Hero slide updated successfully.');
    }

    public function toggle($id)
    {
        $slider = HomeSlider::findOrFail($id);
        $slider->update(['is_active' => !$slider->is_active]);

        return redirect()->route('admin.sliders.index')
            ->with('success', $slider->is_active ? 'Slide is now visible on the home page.' : 'Slide hidden from the home page.');
    }

    public function destroy($id)
    {
        $slider = HomeSlider::findOrFail($id);
        $this->deleteImage($slider->image_path);
        $slider->delete();

        return redirect()->route('admin.sliders.index')->with('success', 'Hero slide deleted.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:150',
            'subtitle'   => 'nullable|string|max:300',
            'cta_label'  => 'nullable|string|max:60|required_with:cta_url',
            'cta_url'    => [
                'nullable', 'string', 'max:255', 'required_with:cta_label',
                function ($attribute, $value, $fail) {
                    $value = trim((string) $value);
                    if ($value === '') {
                        return;
                    }
                    // Only a site-relative path ("/donations") or a secure absolute link is accepted.
                    $isRelative = preg_match('#^/(?!/)[^\s]*$#', $value) === 1;
                    $isHttps = preg_match('#^https://[^\s]+$#i', $value) === 1 && filter_var($value, FILTER_VALIDATE_URL) !== false;
                    if (!$isRelative && !$isHttps) {
                        $fail('The button link must start with / (a page on this site) or https://');
                    }
                },
            ],
            'sort_order' => 'required|integer|min:1|max:999',
            'image'      => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
        ]);

        return [
            'title'      => trim($validated['title']),
            'subtitle'   => isset($validated['subtitle']) ? trim($validated['subtitle']) : null,
            'cta_label'  => !empty($validated['cta_label']) ? trim($validated['cta_label']) : null,
            'cta_url'    => !empty($validated['cta_url']) ? trim($validated['cta_url']) : null,
            'sort_order' => (int) $validated['sort_order'],
            'is_active'  => $request->boolean('is_active'),
        ];
    }

    private function deleteImage(?string $path): void
    {
        if (!empty($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GalleryImageStoreRequest;
use App\Http\Requests\Admin\GalleryImageUpdateRequest;
use App\Models\GalleryImage;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GalleryImageController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', GalleryImage::class);

        $images = GalleryImage::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(30)
            ->withQueryString()
            ->appends(PublicLocale::queryFromRequestOrUser($request->user()));

        return view('admin.gallery-images.index', [
            'images' => $images,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', GalleryImage::class);

        return view('admin.gallery-images.create');
    }

    public function store(GalleryImageStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', GalleryImage::class);

        $path = $request->file('image')->store('gallery', 'public');
        $nextOrder = (int) (GalleryImage::query()->max('sort_order') ?? 0) + 1;

        GalleryImage::query()->create([
            'path' => $path,
            'sort_order' => $nextOrder,
            'alt_text_en' => $request->input('alt_text_en'),
            'alt_text_ar' => $request->input('alt_text_ar'),
            'is_visible' => $request->boolean('is_visible', true),
        ]);

        return redirect()
            ->route('admin.gallery-images.index', PublicLocale::queryFromRequestOrUser($request->user()))
            ->with('status', __('Gallery photo added.'));
    }

    public function edit(GalleryImage $galleryImage): View
    {
        $this->authorize('update', $galleryImage);

        return view('admin.gallery-images.edit', [
            'image' => $galleryImage,
        ]);
    }

    public function update(GalleryImageUpdateRequest $request, GalleryImage $galleryImage): RedirectResponse
    {
        $this->authorize('update', $galleryImage);

        $data = [
            'alt_text_en' => $request->input('alt_text_en'),
            'alt_text_ar' => $request->input('alt_text_ar'),
            'sort_order' => (int) $request->input('sort_order'),
            'is_visible' => $request->boolean('is_visible', true),
        ];

        if ($request->hasFile('image')) {
            $old = $galleryImage->path;
            $path = $request->file('image')->store('gallery', 'public');
            $data['path'] = $path;
            if (filled($old) && $old !== $path) {
                Storage::disk('public')->delete($old);
            }
        }

        $galleryImage->update($data);

        return redirect()
            ->route('admin.gallery-images.index', PublicLocale::queryFromRequestOrUser($request->user()))
            ->with('status', __('Gallery photo updated.'));
    }

    public function destroy(Request $request, GalleryImage $galleryImage): RedirectResponse
    {
        $this->authorize('delete', $galleryImage);

        Storage::disk('public')->delete($galleryImage->path);
        $galleryImage->delete();

        return redirect()
            ->route('admin.gallery-images.index', PublicLocale::queryFromRequestOrUser($request->user()))
            ->with('status', __('Gallery photo removed.'));
    }
}

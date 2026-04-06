<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\View\View;

class GalleryPublicController extends Controller
{
    public function __invoke(): View
    {
        $locale = app()->getLocale();

        $introPage = CmsPage::findPublished('gallery');

        $galleryPages = CmsPage::query()
            ->published()
            ->forLocale($locale)
            ->where('show_in_gallery', true)
            ->whereNotIn('slug', CmsPage::INSTITUTIONAL_SLUGS)
            ->orderByDesc('published_at')
            ->orderBy('title')
            ->get();

        $downloads = config('swaeduae.document_downloads', []);

        return view('public.gallery', [
            'introPage' => $introPage,
            'galleryPages' => $galleryPages,
            'downloads' => $downloads,
        ]);
    }
}

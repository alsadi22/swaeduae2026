<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\View\View;

class ProgramsIndexController extends Controller
{
    public function __invoke(): View
    {
        $locale = app()->getLocale();

        $cmsPage = CmsPage::findPublished('programs');

        $programPages = CmsPage::query()
            ->published()
            ->forLocale($locale)
            ->where('show_on_programs', true)
            ->whereNotIn('slug', CmsPage::INSTITUTIONAL_SLUGS)
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('public.programs', [
            'cmsPage' => $cmsPage,
            'programPages' => $programPages,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutionalPageController extends Controller
{
    /**
     * @var list<string>
     */
    private const ALLOWED_FALLBACK_VIEWS = [
        'public.about',
        'public.media',
        'public.partners',
        'public.faq',
        'public.legal.terms',
        'public.legal.privacy',
        'public.legal.cookies',
    ];

    public function show(Request $request): View
    {
        $slug = $request->route('cms_slug');
        $fallbackView = $request->route('fallback_view');

        if (! is_string($slug) || ! is_string($fallbackView)) {
            abort(404);
        }

        if (! in_array($fallbackView, self::ALLOWED_FALLBACK_VIEWS, true)) {
            abort(404);
        }

        $page = CmsPage::findPublished($slug);

        if ($page !== null) {
            return view('public.cms-page', ['cmsPage' => $page]);
        }

        return view($fallbackView);
    }
}

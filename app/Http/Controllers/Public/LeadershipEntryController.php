<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * /leadership: published CMS page if present; otherwise permanent redirect to About (leadership lives on same page).
 */
final class LeadershipEntryController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $page = CmsPage::findPublished('leadership');
        if ($page !== null) {
            return view('public.cms-page', ['cmsPage' => $page]);
        }

        $q = PublicLocale::queryFromRequestOrUser($request->user());

        return redirect()->route('about', $q, 301);
    }
}

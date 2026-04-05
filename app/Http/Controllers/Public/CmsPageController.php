<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CmsPageController extends Controller
{
    public function show(string $slug): View|RedirectResponse
    {
        if ($slug === 'youth-councils') {
            $target = route('youth-councils', absolute: false);
            $qs = request()->getQueryString();
            if (is_string($qs) && $qs !== '') {
                $target .= '?'.$qs;
            }

            return redirect($target, 301);
        }

        $page = CmsPage::findPublished($slug);

        abort_if($page === null, 404);

        return view('public.cms-page', [
            'cmsPage' => $page,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\View\View;

class YouthCouncilsController extends Controller
{
    public function __invoke(): View
    {
        $cmsPage = CmsPage::findPublished('youth-councils');

        return view('public.youth-councils', [
            'cmsPage' => $cmsPage,
        ]);
    }
}

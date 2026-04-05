<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\ExternalNewsItem;
use App\Models\ExternalNewsSource;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaHubController extends Controller
{
    public function __invoke(Request $request): View
    {
        $page = CmsPage::findPublished('media');
        if ($page !== null) {
            return view('public.cms-page', ['cmsPage' => $page]);
        }

        $locale = app()->getLocale();

        $filter = $request->query('filter', 'all');
        if (! is_string($filter) || ! in_array($filter, ['all', 'internal', 'external'], true)) {
            $filter = 'all';
        }

        $sourceId = $request->query('source_id');
        $sourceId = ctype_digit((string) $sourceId) ? (int) $sourceId : null;

        $internalQuery = CmsPage::query()
            ->published()
            ->forLocale($locale)
            ->whereNotIn('slug', CmsPage::INSTITUTIONAL_SLUGS)
            ->orderByDesc('published_at');

        $externalQuery = ExternalNewsItem::query()
            ->with('source')
            ->visibleInMediaCenter()
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at');

        if ($sourceId !== null) {
            $externalQuery->where('source_id', $sourceId);
        }

        $internalNews = (clone $internalQuery)->limit(40)->get();
        $externalNews = (clone $externalQuery)->limit(40)->get();

        if ($filter === 'internal') {
            $externalNews = collect();
        } elseif ($filter === 'external') {
            $internalNews = collect();
        }

        $sources = ExternalNewsSource::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('public.media-hub', [
            'internalNews' => $internalNews,
            'externalNews' => $externalNews,
            'filter' => $filter,
            'sourceId' => $sourceId,
            'sources' => $sources,
        ]);
    }
}

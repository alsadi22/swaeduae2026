<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\Event;
use App\Models\ExternalNewsItem;
use App\Support\HomeImpactStats;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $locale = app()->getLocale();

        $featuredCmsPages = CmsPage::query()
            ->published()
            ->forLocale($locale)
            ->where('show_on_home', true)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $featuredIds = $featuredCmsPages->pluck('id')->all();

        $latestCmsTeasers = CmsPage::query()
            ->published()
            ->forLocale($locale)
            ->where('show_on_media', true)
            ->whereNotIn('slug', CmsPage::INSTITUTIONAL_SLUGS)
            ->when($featuredIds !== [], fn ($q) => $q->whereNotIn('id', $featuredIds))
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        $externalHomeNews = ExternalNewsItem::query()
            ->with('source')
            ->visibleOnHome()
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->limit(8)
            ->get();

        $homeNewsRows = collect();
        foreach ($latestCmsTeasers as $page) {
            $homeNewsRows->push([
                'kind' => 'internal',
                'featured' => false,
                'sort_at' => $page->published_at,
                'page' => $page,
            ]);
        }
        foreach ($externalHomeNews as $ext) {
            $homeNewsRows->push([
                'kind' => 'external',
                'featured' => (bool) $ext->is_featured,
                'sort_at' => $ext->published_at ?? $ext->original_published_at ?? $ext->fetched_at,
                'item' => $ext,
            ]);
        }

        $homeNewsTeasers = $homeNewsRows
            ->sort(function (array $a, array $b): int {
                $byFeatured = ($b['featured'] ? 1 : 0) <=> ($a['featured'] ? 1 : 0);
                if ($byFeatured !== 0) {
                    return $byFeatured;
                }

                return ($b['sort_at']?->getTimestamp() ?? 0) <=> ($a['sort_at']?->getTimestamp() ?? 0);
            })
            ->values()
            ->take(8);

        $impactStats = HomeImpactStats::snapshot();

        $upcomingEvents = Event::query()
            ->with('organization')
            ->where('event_ends_at', '>=', now())
            ->orderBy('event_starts_at')
            ->limit(5)
            ->get();

        return view('public.home', compact(
            'featuredCmsPages',
            'latestCmsTeasers',
            'homeNewsTeasers',
            'impactStats',
            'upcomingEvents',
        ));
    }
}

<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\Event;
use App\Models\ExternalNewsItem;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        /** @var Collection<int, array{loc: string, lastmod: ?Carbon}> $items */
        $items = collect();

        $push = function (string $loc, ?Carbon $lastmod = null) use ($items): void {
            $items->push(['loc' => $loc, 'lastmod' => $lastmod]);
        };

        foreach ([
            route('home', [], true),
            route('about', [], true),
            route('leadership', [], true),
            route('programs.index', [], true),
            route('youth-councils', [], true),
            route('events.index', [], true),
            route('media.index', [], true),
            route('gallery', [], true),
            route('partners', [], true),
            route('faq', [], true),
            route('volunteer.index', [], true),
            route('volunteer.opportunities.index', [], true),
            route('contact.show', [], true),
            route('support.show', [], true),
            route('legal.terms', [], true),
            route('legal.privacy', [], true),
            route('legal.cookies', [], true),
        ] as $loc) {
            $push($loc, null);
        }

        CmsPage::query()
            ->published()
            ->get()
            ->groupBy('slug')
            ->each(function ($pages) use ($push): void {
                $page = $pages->first();
                $push($page->publicUrl(), $page->updated_at);
            });

        Event::query()
            ->where('event_ends_at', '>=', now())
            ->orderBy('event_starts_at')
            ->get()
            ->each(function (Event $event) use ($push): void {
                $push(route('events.show', $event, true), $event->updated_at);
                $push(route('volunteer.opportunities.show', $event, true), $event->updated_at);
            });

        ExternalNewsItem::query()
            ->published()
            ->where(function ($q): void {
                $q->where('show_in_media_center', true)
                    ->orWhere('show_on_home', true);
            })
            ->orderByDesc('published_at')
            ->get()
            ->each(function (ExternalNewsItem $item) use ($push): void {
                $push(route('media.external.show', $item, true), $item->updated_at);
            });

        $merged = $items
            ->groupBy('loc')
            ->map(function (Collection $group): array {
                $lastmods = $group->pluck('lastmod')->filter();

                return [
                    'loc' => $group->first()['loc'],
                    'lastmod' => $lastmods->isEmpty()
                        ? null
                        : $lastmods->sortBy(fn (Carbon $d): int => $d->getTimestamp())->last(),
                ];
            })
            ->values();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($merged as $row) {
            $xml .= '<url><loc>'.e($row['loc']).'</loc>';
            if ($row['lastmod'] instanceof Carbon) {
                $xml .= '<lastmod>'.$row['lastmod']->timezone('UTC')->toDateString().'</lastmod>';
            }
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}

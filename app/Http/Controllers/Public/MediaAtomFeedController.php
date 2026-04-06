<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\ExternalNewsItem;
use App\Support\PublicLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class MediaAtomFeedController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $locale = app()->getLocale();
        $localeQ = PublicLocale::queryFromRequestOrUser($request->user());

        $entries = collect();

        $internal = CmsPage::query()
            ->published()
            ->forLocale($locale)
            ->where('show_on_media', true)
            ->whereNotIn('slug', CmsPage::INSTITUTIONAL_SLUGS)
            ->orderByDesc('published_at')
            ->limit(30)
            ->get();

        foreach ($internal as $page) {
            $url = $page->absolutePublicUrl($locale);
            $updatedAt = $page->updated_at ?? $page->published_at ?? now();
            $published = $page->published_at ?? $updatedAt;
            $summary = $page->excerpt ?? Str::limit(strip_tags((string) $page->body), 500);
            $entries->push([
                'title' => $page->title,
                'url' => $url,
                'updated' => $updatedAt,
                'published' => $published,
                'summary' => $summary,
            ]);
        }

        $external = ExternalNewsItem::query()
            ->visibleInMediaCenter()
            ->orderByDesc('published_at')
            ->limit(20)
            ->get();

        foreach ($external as $item) {
            $url = $item->absolutePublicUrl($locale);
            $pub = $item->published_at ?? $item->fetched_at ?? now();
            $upd = $item->updated_at ?? $pub;
            $sum = $item->summaryForLocale();
            $entries->push([
                'title' => $item->titleForLocale(),
                'url' => $url,
                'updated' => $upd,
                'published' => $pub,
                'summary' => $sum ? Str::limit(strip_tags($sum), 500) : '',
            ]);
        }

        $entries = $entries
            ->sortByDesc(fn (array $e) => $e['published']->getTimestamp())
            ->take(40)
            ->values();

        $feedUpdated = $entries->isEmpty()
            ? now()
            : $entries->sortByDesc(fn (array $e) => $e['updated']->getTimestamp())->first()['updated'];

        $siteTitle = config('app.name', 'SwaedUAE');
        $mediaIndex = route('media.index', $localeQ, true);
        $selfLink = route('feed', $localeQ, true);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<feed xmlns="http://www.w3.org/2005/Atom">'
            .'<title>'.e($siteTitle.' — '.__('Media center')).'</title>'
            .'<link rel="alternate" type="text/html" href="'.e($mediaIndex).'"/>'
            .'<link rel="self" type="application/atom+xml" href="'.e($selfLink).'"/>'
            .'<id>'.e($selfLink).'</id>'
            .'<updated>'.$feedUpdated->copy()->timezone('UTC')->format('Y-m-d\TH:i:s\Z').'</updated>';

        foreach ($entries as $e) {
            $xml .= '<entry>'
                .'<title>'.e($e['title']).'</title>'
                .'<link rel="alternate" href="'.e($e['url']).'"/>'
                .'<id>'.e($e['url']).'</id>'
                .'<updated>'.$e['updated']->copy()->timezone('UTC')->format('Y-m-d\TH:i:s\Z').'</updated>'
                .'<published>'.$e['published']->copy()->timezone('UTC')->format('Y-m-d\TH:i:s\Z').'</published>'
                .'<summary type="text">'.e($e['summary']).'</summary>'
                .'</entry>';
        }

        $xml .= '</feed>';

        return response($xml, 200)
            ->header('Content-Type', 'application/atom+xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=600');
    }
}

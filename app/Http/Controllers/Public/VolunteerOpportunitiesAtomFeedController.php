<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\PublicLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class VolunteerOpportunitiesAtomFeedController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $locale = app()->getLocale();
        $localeQ = PublicLocale::queryFromRequestOrUser($request->user());

        $events = Event::query()
            ->with('organization')
            ->where('event_ends_at', '>=', now())
            ->orderBy('event_starts_at')
            ->limit(50)
            ->get();

        $siteTitle = config('app.name', 'SwaedUAE');
        $hubUrl = route('volunteer.opportunities.index', $localeQ, true);
        $selfLink = route('volunteer.opportunities.feed', $localeQ, true);

        $feedUpdated = $events->isEmpty()
            ? now()
            : $events->max('updated_at') ?? now();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<feed xmlns="http://www.w3.org/2005/Atom">'
            .'<title>'.e($siteTitle.' — '.__('Volunteer opportunities')).'</title>'
            .'<link rel="alternate" type="text/html" href="'.e($hubUrl).'"/>'
            .'<link rel="self" type="application/atom+xml" href="'.e($selfLink).'"/>'
            .'<id>'.e($selfLink).'</id>'
            .'<updated>'.$feedUpdated->copy()->timezone('UTC')->format('Y-m-d\TH:i:s\Z').'</updated>';

        foreach ($events as $event) {
            $url = route('volunteer.opportunities.show', array_merge(['event' => $event], $localeQ), true);
            $title = $event->titleForLocale();
            $org = $event->organization?->nameForLocale();
            $summaryParts = array_filter([
                $org,
                $event->event_starts_at->locale($locale)->isoFormat('LLL').' — '.$event->event_ends_at->locale($locale)->isoFormat('LLL'),
            ]);
            $summary = Str::limit(implode(' · ', $summaryParts), 500);
            $updated = $event->updated_at ?? $event->created_at ?? now();
            $published = $event->created_at ?? $updated;

            $xml .= '<entry>'
                .'<title>'.e($title).'</title>'
                .'<link rel="alternate" href="'.e($url).'"/>'
                .'<id>'.e($url).'</id>'
                .'<updated>'.$updated->copy()->timezone('UTC')->format('Y-m-d\TH:i:s\Z').'</updated>'
                .'<published>'.$published->copy()->timezone('UTC')->format('Y-m-d\TH:i:s\Z').'</published>'
                .'<summary type="text">'.e($summary).'</summary>'
                .'</entry>';
        }

        $xml .= '</feed>';

        return response($xml, 200)
            ->header('Content-Type', 'application/atom+xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=600');
    }
}

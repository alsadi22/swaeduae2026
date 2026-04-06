<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\Event;
use App\Support\PublicLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicEventController extends Controller
{
    public function index(Request $request): View
    {
        $cmsPage = CmsPage::findPublished('events');

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        $searchInput = isset($validated['q']) ? trim((string) $validated['q']) : '';
        $searchTerm = $searchInput === '' ? null : mb_substr($searchInput, 0, 120);

        $rawSort = $request->query('sort');
        $sort = is_string($rawSort) && in_array($rawSort, ['starts_asc', 'starts_desc', 'title_asc'], true)
            ? $rawSort
            : 'starts_asc';

        $query = Event::query()
            ->with('organization')
            ->withCount('volunteers')
            ->where('event_ends_at', '>=', now());

        if ($searchTerm !== null) {
            $query->where(function ($q) use ($searchTerm): void {
                $q->where(function ($qq) use ($searchTerm): void {
                    $qq->whereRaw('strpos(lower(title_en::text), lower(?::text)) > 0', [$searchTerm])
                        ->orWhereRaw('strpos(lower(title_ar::text), lower(?::text)) > 0', [$searchTerm]);
                })->orWhereHas('organization', function ($qq) use ($searchTerm): void {
                    $qq->whereRaw('strpos(lower(name_en::text), lower(?::text)) > 0', [$searchTerm])
                        ->orWhereRaw('strpos(lower(name_ar::text), lower(?::text)) > 0', [$searchTerm]);
                });
            });
        }

        $appLocale = app()->getLocale();
        match ($sort) {
            'starts_desc' => $query->orderByDesc('event_starts_at'),
            'title_asc' => $appLocale === 'ar'
                ? $query->orderByRaw('lower(title_ar::text)')->orderByRaw('lower(title_en::text)')
                : $query->orderByRaw('lower(title_en::text)')->orderByRaw('lower(title_ar::text)'),
            default => $query->orderBy('event_starts_at'),
        };

        $events = $query->paginate(15)->withQueryString()->appends(PublicLocale::queryFromRequestOrUser($request->user()));

        $pageTitle = $cmsPage
            ? $cmsPage->title.' — '.__('SwaedUAE')
            : __('Events').' — '.__('SwaedUAE');

        $metaDescription = $cmsPage?->meta_description
            ?? $cmsPage?->excerpt
            ?? __('site.meta_description');

        $search = $searchInput;

        return view('public.events-index', compact('cmsPage', 'events', 'pageTitle', 'metaDescription', 'search', 'sort'));
    }

    public function show(Event $event): View
    {
        $event->load('organization');
        $event->loadCount('volunteers');

        $pageTitle = $event->titleForLocale().' — '.__('Events').' — '.__('SwaedUAE');

        return view('public.events.show', [
            'event' => $event,
            'pageTitle' => $pageTitle,
            'metaDescription' => __('site.events_detail_meta', ['event' => $event->titleForLocale()]),
            'isPast' => $event->event_ends_at->isPast(),
        ]);
    }

    /**
     * RFC 5545 iCalendar for the public event (UTC timestamps).
     */
    public function ics(Event $event): Response
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'swaeduae.ae';
        $uid = $event->uuid.'@'.$host;
        $summary = $this->icsEscapeText($event->titleForLocale());
        $descriptionParts = array_filter([
            $event->organization?->nameForLocale(),
            __('Volunteer with SwaedUAE').' — '.route('volunteer.opportunities.show', ['event' => $event], true),
        ]);
        $description = $this->icsEscapeText(implode("\n", $descriptionParts));
        $dtStamp = now()->utc()->format('Ymd\THis\Z');
        $dtStart = $event->event_starts_at->clone()->utc()->format('Ymd\THis\Z');
        $dtEnd = $event->event_ends_at->clone()->utc()->format('Ymd\THis\Z');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//SwaedUAE//Public Event//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.$dtStamp,
            'DTSTART:'.$dtStart,
            'DTEND:'.$dtEnd,
            'SUMMARY:'.$summary,
            'DESCRIPTION:'.$description,
            'URL:'.route('events.show', ['event' => $event], true),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        $body = implode("\r\n", $lines)."\r\n";
        $filename = Str::slug($event->title_en ?: 'event').'-'.$event->uuid.'.ics';

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function icsEscapeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r", "\n"], '\n', $text);

        return str_replace(['\\', ';', ','], ['\\\\', '\\;', '\\,'], $text);
    }
}

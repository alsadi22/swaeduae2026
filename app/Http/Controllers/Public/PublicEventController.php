<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\Event;
use App\Support\PublicLocale;
use Illuminate\Http\Request;
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

        $events = $query->orderBy('event_starts_at')->paginate(15)->withQueryString()->appends(PublicLocale::query());

        $pageTitle = $cmsPage
            ? $cmsPage->title.' — '.__('SwaedUAE')
            : __('Events').' — '.__('SwaedUAE');

        $metaDescription = $cmsPage?->meta_description
            ?? $cmsPage?->excerpt
            ?? __('site.meta_description');

        $search = $searchInput;

        return view('public.events-index', compact('cmsPage', 'events', 'pageTitle', 'metaDescription', 'search'));
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
}

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
    private const PER_PAGE = 12;

    public function __invoke(Request $request): View
    {
        $page = CmsPage::findPublished('media');
        if ($page !== null) {
            return view('public.cms-page', ['cmsPage' => $page]);
        }

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        $searchInput = isset($validated['q']) ? trim((string) $validated['q']) : '';
        $searchTerm = $searchInput === '' ? null : mb_substr($searchInput, 0, 120);

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
            ->where('show_on_media', true)
            ->whereNotIn('slug', CmsPage::INSTITUTIONAL_SLUGS)
            ->orderByDesc('published_at');

        $externalQuery = ExternalNewsItem::query()
            ->with('source')
            ->visibleInMediaCenter()
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at');

        if ($searchTerm !== null) {
            $internalQuery->where(function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(title::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(coalesce(excerpt::text, \'\')), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(body::text), lower(?::text)) > 0', [$searchTerm]);
            });
            $externalQuery->where(function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(coalesce(normalized_title_en::text, \'\')), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(coalesce(normalized_title_ar::text, \'\')), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(coalesce(original_title::text, \'\')), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(coalesce(normalized_summary_en::text, \'\')), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(coalesce(normalized_summary_ar::text, \'\')), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(coalesce(original_summary::text, \'\')), lower(?::text)) > 0', [$searchTerm]);
            });
        }

        if ($sourceId !== null) {
            $externalQuery->where('source_id', $sourceId);
        }

        if ($filter === 'internal') {
            $internalPaginator = (clone $internalQuery)->paginate(self::PER_PAGE)->withQueryString();
            $externalPaginator = null;
        } elseif ($filter === 'external') {
            $internalPaginator = null;
            $externalPaginator = (clone $externalQuery)->paginate(self::PER_PAGE)->withQueryString();
        } else {
            $internalPaginator = (clone $internalQuery)
                ->paginate(self::PER_PAGE, ['*'], 'internal_page')
                ->withQueryString();
            $externalPaginator = (clone $externalQuery)
                ->paginate(self::PER_PAGE, ['*'], 'external_page')
                ->withQueryString();
        }

        $sources = ExternalNewsSource::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('public.media-hub', [
            'internalPaginator' => $internalPaginator,
            'externalPaginator' => $externalPaginator,
            'filter' => $filter,
            'sourceId' => $sourceId,
            'sources' => $sources,
            'search' => $searchInput,
            'searchActive' => $searchTerm !== null,
        ]);
    }
}

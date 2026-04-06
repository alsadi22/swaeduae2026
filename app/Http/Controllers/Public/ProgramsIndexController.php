<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Support\PublicLocale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramsIndexController extends Controller
{
    public function __invoke(Request $request): View
    {
        $locale = app()->getLocale();

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        $searchInput = isset($validated['q']) ? trim((string) $validated['q']) : '';
        $searchTerm = $searchInput === '' ? null : mb_substr($searchInput, 0, 120);

        $rawSort = $request->query('sort');
        $sort = is_string($rawSort) && in_array($rawSort, ['published_desc', 'published_asc', 'title_asc'], true)
            ? $rawSort
            : 'published_desc';

        $cmsPage = CmsPage::findPublished('programs');

        $query = CmsPage::query()
            ->published()
            ->forLocale($locale)
            ->where('show_on_programs', true)
            ->whereNotIn('slug', CmsPage::INSTITUTIONAL_SLUGS);

        if ($searchTerm !== null) {
            $query->where(function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(title::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw("strpos(lower(COALESCE(excerpt, '')::text), lower(?::text)) > 0", [$searchTerm])
                    ->orWhereRaw('strpos(lower(body::text), lower(?::text)) > 0', [$searchTerm]);
            });
        }

        match ($sort) {
            'published_asc' => $query->orderBy('published_at')->orderBy('title'),
            'title_asc' => $query->orderBy('title'),
            default => $query->orderByDesc('published_at')->orderBy('title'),
        };

        $programPages = $query
            ->paginate(12)
            ->withQueryString()
            ->appends(PublicLocale::queryFromRequestOrUser($request->user()));

        return view('public.programs', [
            'cmsPage' => $cmsPage,
            'programPages' => $programPages,
            'search' => $searchInput,
            'sort' => $sort,
        ]);
    }
}

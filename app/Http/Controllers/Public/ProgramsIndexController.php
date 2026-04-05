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

        $programPages = $query
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString()
            ->appends(PublicLocale::query());

        return view('public.programs', [
            'cmsPage' => $cmsPage,
            'programPages' => $programPages,
            'search' => $searchInput,
        ]);
    }
}

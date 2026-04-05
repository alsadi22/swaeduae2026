<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CmsPageStoreRequest;
use App\Http\Requests\Admin\CmsPageUpdateRequest;
use App\Models\CmsPage;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CmsPageController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CmsPage::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'placement' => ['nullable', 'string', 'in:all,home,programs,media'],
        ]);
        $searchInput = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $searchTerm = $searchInput === '' ? null : $searchInput;

        $placement = $validated['placement'] ?? 'all';

        $query = CmsPage::query()->orderByDesc('updated_at');

        if ($searchTerm !== null) {
            $query->where(function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(title::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(slug::text), lower(?::text)) > 0', [$searchTerm]);
            });
        }

        if ($placement === 'home') {
            $query->where('show_on_home', true);
        } elseif ($placement === 'programs') {
            $query->where('show_on_programs', true);
        } elseif ($placement === 'media') {
            $query->where('show_on_media', true);
        }

        $pages = $query->paginate(20)->withQueryString()->appends(PublicLocale::query());

        return view('admin.cms-pages.index', [
            'pages' => $pages,
            'search' => $searchInput,
            'placement' => $placement,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CmsPage::class);

        return view('admin.cms-pages.create', ['page' => new CmsPage]);
    }

    public function store(CmsPageStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['author_id'] = $request->user()->id;

        CmsPage::query()->create($data);

        return redirect()
            ->route('admin.cms-pages.index', PublicLocale::query())
            ->with('status', __('CMS page created.'));
    }

    public function edit(CmsPage $cms_page): View
    {
        $this->authorize('update', $cms_page);

        return view('admin.cms-pages.edit', ['page' => $cms_page]);
    }

    /**
     * Render saved CMS content as visitors would see it, including drafts (not on the public URL).
     */
    public function preview(CmsPage $cms_page): View
    {
        $this->authorize('view', $cms_page);

        app()->setLocale($cms_page->locale);

        if ($cms_page->slug === 'youth-councils') {
            return view('public.youth-councils', [
                'cmsPage' => $cms_page,
                'previewMode' => true,
            ]);
        }

        return view('public.cms-page', [
            'cmsPage' => $cms_page,
            'previewMode' => true,
        ]);
    }

    public function update(CmsPageUpdateRequest $request, CmsPage $cms_page): RedirectResponse
    {
        $cms_page->update($request->validated());

        return redirect()
            ->route('admin.cms-pages.index', PublicLocale::query())
            ->with('status', __('CMS page updated.'));
    }

    public function destroy(CmsPage $cms_page): RedirectResponse
    {
        $this->authorize('delete', $cms_page);

        $cms_page->delete();

        return redirect()
            ->route('admin.cms-pages.index', PublicLocale::query())
            ->with('status', __('CMS page deleted.'));
    }
}

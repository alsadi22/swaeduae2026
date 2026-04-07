<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CmsPageStoreRequest;
use App\Http\Requests\Admin\CmsPageUpdateRequest;
use App\Models\CmsPage;
use App\Support\AdminCmsOgImage;
use App\Support\PublicLocale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CmsPageController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CmsPage::class);

        $state = $this->validatedCmsListFilters($request);

        $pages = $this->cmsPagesListQuery($state)
            ->paginate(20)
            ->withQueryString()
            ->appends(PublicLocale::queryFromRequestOrUser($request->user()));

        return view('admin.cms-pages.index', [
            'pages' => $pages,
            'search' => $state['search_input'],
            'placement' => $state['placement'],
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', CmsPage::class);

        $state = $this->validatedCmsListFilters($request);

        $rows = $this->cmsPagesListQuery($state)->get();

        $filtered = $state['search_term'] !== null || $state['placement'] !== 'all';
        $filename = 'cms-pages-admin'.($filtered ? '-filtered' : '').'-'.now()->format('Y-m-d').'.csv';

        $tz = config('app.timezone');

        return response()->streamDownload(function () use ($rows, $tz): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'id',
                __('Title'),
                __('Slug'),
                __('Lang'),
                __('Status'),
                __('Show on home page'),
                __('Show on programs page'),
                __('Show in media center'),
                __('Show in gallery'),
                __('Published at'),
                __('Updated'),
            ]);
            foreach ($rows as $p) {
                fputcsv($out, [
                    (string) $p->id,
                    $p->title,
                    $p->slug,
                    $p->locale,
                    $p->status,
                    $p->show_on_home ? '1' : '0',
                    $p->show_on_programs ? '1' : '0',
                    $p->show_on_media ? '1' : '0',
                    $p->show_in_gallery ? '1' : '0',
                    $p->published_at?->timezone($tz)->format('Y-m-d H:i') ?? '',
                    $p->updated_at?->timezone($tz)->format('Y-m-d H:i') ?? '',
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CmsPage::class);

        return view('admin.cms-pages.create', ['page' => new CmsPage]);
    }

    public function store(CmsPageStoreRequest $request): RedirectResponse
    {
        $data = collect($request->validated())
            ->except(['og_image_upload', 'remove_og_image'])
            ->all();
        $data['author_id'] = $request->user()->id;

        $upload = $request->file('og_image_upload');
        if ($upload !== null) {
            $data['og_image'] = null;
        }

        $page = CmsPage::query()->create($data);

        if ($upload !== null) {
            $path = $upload->store('cms/og/'.$page->id, 'public');
            $page->update(['og_image' => '/storage/'.$path]);
        }

        return redirect()
            ->route('admin.cms-pages.index', PublicLocale::queryFromRequestOrUser($request->user()))
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
        $data = collect($request->validated())
            ->except(['og_image_upload', 'remove_og_image'])
            ->all();

        if ($request->hasFile('og_image_upload')) {
            AdminCmsOgImage::deleteIfManaged($cms_page->og_image);
            $path = $request->file('og_image_upload')->store('cms/og/'.$cms_page->id, 'public');
            $data['og_image'] = '/storage/'.$path;
        } elseif ($request->boolean('remove_og_image')) {
            AdminCmsOgImage::deleteIfManaged($cms_page->og_image);
            $data['og_image'] = null;
        }

        $cms_page->update($data);

        return redirect()
            ->route('admin.cms-pages.index', PublicLocale::queryFromRequestOrUser($request->user()))
            ->with('status', __('CMS page updated.'));
    }

    public function destroy(Request $request, CmsPage $cms_page): RedirectResponse
    {
        $this->authorize('delete', $cms_page);

        AdminCmsOgImage::deleteIfManaged($cms_page->og_image);
        $cms_page->delete();

        return redirect()
            ->route('admin.cms-pages.index', PublicLocale::queryFromRequestOrUser($request->user()))
            ->with('status', __('CMS page deleted.'));
    }

    /**
     * @return array{search_input: string, search_term: string|null, placement: string}
     */
    private function validatedCmsListFilters(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'placement' => ['nullable', 'string', 'in:all,home,programs,media,gallery'],
        ]);

        $searchInput = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $searchTerm = $searchInput === '' ? null : $searchInput;
        $placement = $validated['placement'] ?? 'all';

        return [
            'search_input' => $searchInput,
            'search_term' => $searchTerm,
            'placement' => $placement,
        ];
    }

    /**
     * @param  array{search_term: string|null, placement: string}  $state
     * @return Builder<CmsPage>
     */
    private function cmsPagesListQuery(array $state): Builder
    {
        $query = CmsPage::query()->orderByDesc('updated_at');

        if ($state['search_term'] !== null) {
            $searchTerm = $state['search_term'];
            $query->where(function ($q) use ($searchTerm): void {
                $q->whereRaw('strpos(lower(title::text), lower(?::text)) > 0', [$searchTerm])
                    ->orWhereRaw('strpos(lower(slug::text), lower(?::text)) > 0', [$searchTerm]);
            });
        }

        if ($state['placement'] === 'home') {
            $query->where('show_on_home', true);
        } elseif ($state['placement'] === 'programs') {
            $query->where('show_on_programs', true);
        } elseif ($state['placement'] === 'media') {
            $query->where('show_on_media', true);
        } elseif ($state['placement'] === 'gallery') {
            $query->where('show_in_gallery', true);
        }

        return $query;
    }
}

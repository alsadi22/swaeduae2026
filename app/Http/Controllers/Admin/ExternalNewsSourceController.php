<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\FetchExternalNewsSourceJob;
use App\Models\ExternalNewsFetchLog;
use App\Models\ExternalNewsSource;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExternalNewsSourceController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ExternalNewsSource::class);

        $sources = ExternalNewsSource::query()
            ->with('latestFetchLog')
            ->orderByDesc('priority')
            ->orderBy('name')
            ->get();

        return view('admin.external-news.sources.index', compact('sources'));
    }

    public function create(): View
    {
        $this->authorize('create', ExternalNewsSource::class);

        return view('admin.external-news.sources.create', ['source' => new ExternalNewsSource]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ExternalNewsSource::class);

        $data = $this->validatedSource($request);
        $data['created_by_user_id'] = $request->user()->id;
        if (trim((string) ($data['slug'] ?? '')) === '') {
            $data['slug'] = $this->uniqueSlugFromName($data['name']);
        }

        ExternalNewsSource::query()->create($data);

        return redirect()
            ->route('admin.external-news-sources.index', PublicLocale::query())
            ->with('status', __('News source created.'));
    }

    public function edit(ExternalNewsSource $external_news_source): View
    {
        $this->authorize('update', $external_news_source);

        return view('admin.external-news.sources.edit', ['source' => $external_news_source]);
    }

    public function update(Request $request, ExternalNewsSource $external_news_source): RedirectResponse
    {
        $this->authorize('update', $external_news_source);

        $data = $this->validatedSource($request, $external_news_source->id);
        if (trim((string) ($data['slug'] ?? '')) === '') {
            unset($data['slug']);
        }

        $external_news_source->update($data);

        return redirect()
            ->route('admin.external-news-sources.index', PublicLocale::query())
            ->with('status', __('News source updated.'));
    }

    public function destroy(ExternalNewsSource $external_news_source): RedirectResponse
    {
        $this->authorize('delete', $external_news_source);

        $external_news_source->delete();

        return redirect()
            ->route('admin.external-news-sources.index', PublicLocale::query())
            ->with('status', __('News source removed.'));
    }

    public function fetch(ExternalNewsSource $external_news_source): RedirectResponse
    {
        $this->authorize('update', $external_news_source);

        if (! $external_news_source->supportsRssOrAtom()) {
            return back()->withErrors(['fetch' => __('This source type cannot be fetched yet.')]);
        }

        FetchExternalNewsSourceJob::dispatchSync($external_news_source->id);

        return back()->with('status', __('Fetch completed. Check logs for details.'));
    }

    public function logs(ExternalNewsSource $external_news_source): View
    {
        $this->authorize('view', $external_news_source);

        $logs = ExternalNewsFetchLog::query()
            ->where('source_id', $external_news_source->id)
            ->orderByDesc('started_at')
            ->paginate(30)
            ->withQueryString()
            ->appends(PublicLocale::query());

        return view('admin.external-news.sources.logs', [
            'source' => $external_news_source,
            'logs' => $logs,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedSource(Request $request, ?int $ignoreId = null): array
    {
        $type = (string) $request->input('type');
        $rssOrAtom = in_array($type, [ExternalNewsSource::TYPE_RSS, ExternalNewsSource::TYPE_ATOM], true);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('external_news_sources', 'slug')->ignore($ignoreId),
            ],
            'type' => ['required', 'string', Rule::in([
                ExternalNewsSource::TYPE_RSS,
                ExternalNewsSource::TYPE_ATOM,
                ExternalNewsSource::TYPE_API,
                ExternalNewsSource::TYPE_HTML_PARSER,
                ExternalNewsSource::TYPE_MANUAL,
            ])],
            'website_url' => ['nullable', 'string', 'max:2048'],
            'source_logo' => ['nullable', 'string', 'max:2048'],
            'label_en' => ['required', 'string', 'max:255'],
            'label_ar' => ['required', 'string', 'max:255'],
            'fetch_interval_minutes' => ['required', 'integer', 'min:5', 'max:10080'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ];

        $rules['endpoint_url'] = $rssOrAtom
            ? ['required', 'url', 'max:2048']
            : ['nullable', 'string', 'max:2048'];

        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active');
        $data['priority'] = (int) ($request->input('priority') ?? 0);

        return $data;
    }

    private function uniqueSlugFromName(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'source';
        }

        $slug = $base;
        $n = 2;
        while (ExternalNewsSource::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$n;
            $n++;
        }

        return $slug;
    }
}

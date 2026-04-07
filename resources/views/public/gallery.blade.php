@php
    use App\Support\PublicLocale;
    use App\Support\SwaedUaeStructuredData;

    /** @var \App\Models\CmsPage|null $introPage */
    /** @var \Illuminate\Support\Collection<int, \App\Models\CmsPage> $galleryPages */
    /** @var array<int, array<string, mixed>> $downloads */
    $localeQ = PublicLocale::queryFromRequestOrUser(auth()->user());
    $breadcrumbItems = [
        ['name' => __('Home'), 'url' => route('home', $localeQ, true)],
        ['name' => __('Gallery'), 'url' => route('gallery', $localeQ, true)],
    ];
    $pageTitle = $introPage
        ? $introPage->title.' — '.__('SwaedUAE')
        : __('Gallery').' — '.__('SwaedUAE');
    $metaDescription = $introPage
        ? ($introPage->meta_description ?? $introPage->excerpt)
        : __('site.gallery_meta_description');
    $pageAbsoluteUrl = route('gallery', $localeQ, true);
    $ogTitle = $introPage ? $introPage->title : __('Gallery');
    $ogDescription = $metaDescription ?? __('site.meta_description');
    $ogImage = $introPage ? $introPage->resolvedOgImageUrl() : \App\Models\CmsPage::resolveShareImageUrl(config('swaeduae.default_og_image_url'));
    $galleryExtraJsonLd = $introPage !== null
        ? SwaedUaeStructuredData::cmsArticleForJsonLd($introPage, $pageAbsoluteUrl)
        : null;
@endphp
<x-public-layout
    :title="$pageTitle"
    :metaDescription="$metaDescription"
    :ogUrl="$pageAbsoluteUrl"
    :canonicalUrl="$pageAbsoluteUrl"
    :ogTitle="$ogTitle"
    :ogDescription="$ogDescription"
    :ogImage="$ogImage"
    :ogType="$introPage !== null ? 'article' : 'website'"
    :breadcrumbItems="$breadcrumbItems"
    :extraJsonLd="$galleryExtraJsonLd"
>
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        @if ($introPage)
            <header class="mx-auto max-w-3xl border-b border-slate-200 pb-8">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-800">{{ __('SwaedUAE') }}</p>
                <h1 class="public-page-title mt-3">{{ $introPage->title }}</h1>
                @if ($introPage->excerpt)
                    <p class="mt-6 text-lg text-slate-600 leading-relaxed">{{ $introPage->excerpt }}</p>
                @endif
            </header>
            @if (filled($introPage->body))
                <div class="cms-body prose prose-slate mx-auto mt-10 max-w-3xl prose-headings:font-display prose-headings:text-emerald-950 prose-a:text-emerald-800 prose-a:font-medium">
                    {!! str($introPage->body)->markdown() !!}
                </div>
            @endif
        @else
            <div class="max-w-3xl">
                <h1 class="public-page-title">{{ __('Gallery') }}</h1>
                <p class="mt-8 text-slate-600 leading-relaxed">{{ __('site.gallery_page_intro') }}</p>
            </div>
        @endif

        <p class="mt-8">
            <a href="{{ route('media.index', $localeQ) }}" class="text-sm font-bold text-emerald-800 hover:underline">{{ __('Media center') }} →</a>
        </p>
        <div class="mt-4">
            <x-copy-filtered-list-url-button class="max-sm:[&_button]:w-full" test-id="gallery-copy-page-url" />
        </div>

        <section class="{{ $introPage ? 'mt-16 border-t border-slate-200 pt-14' : 'mt-12' }}" aria-labelledby="gallery-grid-heading">
            <h2 id="gallery-grid-heading" class="font-display text-xl font-bold text-emerald-950 sm:text-2xl">
                {{ __('Photos and stories') }}
            </h2>
            <p class="mt-3 max-w-2xl text-sm text-slate-600">{{ __('site.gallery_grid_hint') }}</p>

            @if ($galleryPages->isEmpty())
                <div class="mt-10 rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center text-slate-600">
                    {{ __('site.gallery_placeholder') }}
                </div>
            @else
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($galleryPages as $p)
                        <article class="card-surface group overflow-hidden transition duration-200 hover:shadow-card-hover">
                            <a href="{{ $p->absolutePublicUrl() }}" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                                <div class="h-32 bg-gradient-to-br from-amber-100 via-slate-50 to-emerald-50 transition group-hover:from-amber-200/90"></div>
                                <div class="p-5">
                                    <h3 class="font-display font-bold text-slate-900 group-hover:text-emerald-900">{{ $p->title }}</h3>
                                    @if ($p->excerpt)
                                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ \Illuminate\Support\Str::limit($p->excerpt, 160) }}</p>
                                    @else
                                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ \Illuminate\Support\Str::limit($p->body, 160) }}</p>
                                    @endif
                                    <span class="mt-4 inline-flex text-sm font-bold text-emerald-800 group-hover:underline">{{ __('Read more') }} →</span>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        @if (count($downloads) > 0)
            <section class="mt-16 border-t border-slate-200 pt-14" aria-labelledby="gallery-downloads-heading">
                <h2 id="gallery-downloads-heading" class="font-display text-xl font-bold text-emerald-950">{{ __('Reports and downloads') }}</h2>
                <p class="mt-3 max-w-2xl text-sm text-slate-600">{{ __('site.gallery_downloads_intro') }}</p>
                <ul class="mt-8 space-y-3">
                    @foreach ($downloads as $item)
                        @php
                            $dlabel = app()->getLocale() === 'ar' && ! empty($item['label_ar']) ? $item['label_ar'] : ($item['label'] ?? '');
                            $durl = $item['url'] ?? '#';
                        @endphp
                        <li>
                            <a href="{{ $durl }}" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-800 hover:text-emerald-950 hover:underline" @if(\Illuminate\Support\Str::startsWith($durl, ['http://', 'https://'])) target="_blank" rel="noopener noreferrer" @endif>
                                {{ $dlabel }}
                                <span aria-hidden="true">→</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <p class="mt-12 border-t border-slate-200 pt-10 text-center">
            <a href="{{ route('volunteer.opportunities.index', $localeQ) }}" class="text-sm font-bold text-emerald-800 hover:underline" data-testid="gallery-footer-opportunities">{{ __('Browse opportunities') }} →</a>
        </p>
    </div>
</x-public-layout>

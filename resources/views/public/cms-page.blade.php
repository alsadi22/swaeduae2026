@php
    use App\Support\SwaedUaeStructuredData;

    $pageTitle = $cmsPage->title.' — '.__('SwaedUAE');
    $metaDescription = $cmsPage->meta_description ?? $cmsPage->excerpt;
    $pageAbsoluteUrl = ($previewMode ?? false) ? null : $cmsPage->absolutePublicUrl();
    $ogDescription = $metaDescription ?? __('site.meta_description');
    $ogImage = $cmsPage->resolvedOgImageUrl();
    $breadcrumbItems = null;
    $extraJsonLd = null;
    if (empty($previewMode ?? false)) {
        $localeQ = \App\Support\PublicLocale::query();
        $breadcrumbItems = [
            ['name' => __('Home'), 'url' => route('home', $localeQ, true)],
            ['name' => $cmsPage->title, 'url' => $cmsPage->absolutePublicUrl()],
        ];
        $extraJsonLd = SwaedUaeStructuredData::cmsArticleForJsonLd($cmsPage, $cmsPage->absolutePublicUrl());
    }
@endphp
<x-public-layout
    :title="$pageTitle"
    :metaDescription="$metaDescription"
    :ogUrl="$pageAbsoluteUrl"
    :canonicalUrl="$pageAbsoluteUrl"
    :ogTitle="$cmsPage->title"
    :ogDescription="$ogDescription"
    :ogImage="$ogImage"
    ogType="article"
    :breadcrumbItems="$breadcrumbItems"
    :extraJsonLd="$extraJsonLd"
>
    @if (! empty($previewMode))
        <div class="border-b border-amber-200 bg-amber-50">
            <div class="mx-auto flex max-w-content flex-col gap-2 px-4 py-3 text-sm text-amber-950 sm:px-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold">{{ __('Preview mode') }}</p>
                    <p class="mt-0.5 text-amber-900">{{ __('Visitors only see this page when it is published with a past or current publish date.') }}</p>
                </div>
                <a href="{{ route('admin.cms-pages.edit', $cmsPage) }}" class="shrink-0 font-medium text-amber-950 underline decoration-amber-700 underline-offset-2 hover:text-amber-900">
                    {{ __('Back to editor') }}
                </a>
            </div>
        </div>
    @endif
    <article class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <div class="mx-auto max-w-3xl">
            <header class="border-b border-slate-200 pb-8">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-800">{{ __('SwaedUAE') }}</p>
                <h1 class="public-page-title mt-3">{{ $cmsPage->title }}</h1>
                @if ($cmsPage->excerpt)
                    <p class="mt-6 text-lg text-slate-600 leading-relaxed">{{ $cmsPage->excerpt }}</p>
                @endif
            </header>
            <div class="cms-body prose prose-slate mt-10 max-w-none prose-headings:font-display prose-headings:text-emerald-950 prose-a:text-emerald-800 prose-a:font-medium">
                {!! str($cmsPage->body)->markdown() !!}
            </div>
        </div>
    </article>
</x-public-layout>

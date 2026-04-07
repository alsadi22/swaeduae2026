@php
    use App\Models\CmsPage;
    use App\Support\PublicBreadcrumbs;
    use App\Support\PublicLocale;

    $previewMode = $previewMode ?? false;
    $cmsPage = $cmsPage ?? null;

    if ($cmsPage !== null) {
        $pageTitle = $cmsPage->title.' — '.__('SwaedUAE');
        $metaDescription = $cmsPage->meta_description ?? $cmsPage->excerpt;
        $pageAbsoluteUrl = $previewMode ? null : $cmsPage->absolutePublicUrl();
        $ogTitle = $cmsPage->title;
        $ogDescription = $metaDescription ?? __('site.meta_description');
        $ogImage = $cmsPage->resolvedOgImageUrl();
        $heroTitle = $cmsPage->title;
        $heroSubtitle = $cmsPage->excerpt;
        $mainBodyHtml = str($cmsPage->body)->markdown();
        $heroImageUrl = CmsPage::resolveShareImageUrl($cmsPage->og_image);
    } else {
        $fb = trans('site.youth_councils_fallback');
        $pageTitle = $fb['title'].' — '.__('SwaedUAE');
        $metaDescription = $fb['meta_description'];
        $baseCanonical = route('youth-councils', absolute: true);
        $pageAbsoluteUrl = $baseCanonical.(str_contains($baseCanonical, '?') ? '&' : '?').'lang='.rawurlencode(app()->getLocale());
        $ogTitle = $fb['title'];
        $ogDescription = $metaDescription;
        $ogImage = CmsPage::resolveShareImageUrl(config('swaeduae.default_og_image_url'));
        $heroTitle = $fb['title'];
        $heroSubtitle = $fb['subtitle'];
        $mainBodyHtml = null;
        $heroImageUrl = null;
    }

    $youthEmail = config('swaeduae.mail.youth_councils');
    $localeQ = PublicLocale::queryFromRequestOrUser(auth()->user());
    $breadcrumbItems = null;
    if (! $previewMode) {
        $breadcrumbItems = PublicBreadcrumbs::homeAndCurrent(
            $cmsPage !== null ? $cmsPage->title : $heroTitle,
            $cmsPage !== null ? $cmsPage->absolutePublicUrl() : $pageAbsoluteUrl
        );
    }
@endphp
<x-public-layout
    :title="$pageTitle"
    :metaDescription="$metaDescription"
    :ogUrl="$pageAbsoluteUrl"
    :canonicalUrl="$pageAbsoluteUrl"
    :ogTitle="$ogTitle"
    :ogDescription="$ogDescription"
    :ogImage="$ogImage"
    ogType="article"
    :breadcrumbItems="$breadcrumbItems"
>
    @if ($previewMode)
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
        <header class="mx-auto max-w-3xl border-b border-slate-200 pb-8">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-800">{{ __('SwaedUAE') }}</p>
            <h1 class="public-page-title mt-3">{{ $heroTitle }}</h1>
            @if ($heroSubtitle)
                <p class="mt-6 text-lg text-slate-600 leading-relaxed">{{ $heroSubtitle }}</p>
            @endif
        </header>

        @if (! $previewMode)
            <div class="mx-auto mt-6 max-w-3xl">
                <x-copy-filtered-list-url-button class="max-sm:[&_button]:w-full" test-id="youth-councils-copy-page-url" />
            </div>
        @endif

        @if ($heroImageUrl && $cmsPage !== null)
            <div class="mx-auto mt-10 max-w-3xl">
                <img
                    src="{{ $heroImageUrl }}"
                    alt=""
                    class="max-h-[min(24rem,50vh)] w-full rounded-2xl border border-slate-200/80 object-cover object-center shadow-sm"
                    loading="lazy"
                    decoding="async"
                >
            </div>
        @endif

        <div class="mx-auto max-w-3xl">
            @if ($mainBodyHtml !== null)
                <div class="cms-body prose prose-slate mt-10 max-w-none prose-headings:font-display prose-headings:text-emerald-950 prose-a:text-emerald-800 prose-a:font-medium">
                    {!! $mainBodyHtml !!}
                </div>
            @else
                @foreach ($fb['sections'] as $section)
                    <section class="mt-10 border-t border-slate-100 pt-10 first:mt-10 first:border-t-0 first:pt-0">
                        <h2 class="font-display text-xl font-bold text-emerald-950 sm:text-2xl">{{ $section['heading'] }}</h2>
                        <div class="cms-body prose prose-slate mt-4 max-w-none prose-headings:font-display prose-headings:text-emerald-950 prose-a:text-emerald-800 prose-a:font-medium">
                            {!! str($section['body_md'])->markdown() !!}
                        </div>
                    </section>
                @endforeach
            @endif
        </div>

        <section class="mx-auto mt-14 max-w-3xl border-t border-slate-200 pt-12" aria-labelledby="youth-related-heading">
            <h2 id="youth-related-heading" class="font-display text-xl font-bold text-emerald-950 sm:text-2xl">{{ __('Youth councils related pages') }}</h2>
            <ul class="mt-6 grid list-none grid-cols-1 gap-3 p-0 text-sm font-semibold text-emerald-900 sm:grid-cols-2 sm:items-stretch sm:gap-4">
                <li class="min-w-0">
                    <a href="{{ route('about', $localeQ) }}" class="flex min-h-[3.25rem] w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-3 text-center leading-snug shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50/50">{{ __('About the Association') }}</a>
                </li>
                <li class="min-w-0">
                    <a href="{{ route('programs.index', $localeQ) }}" class="flex min-h-[3.25rem] w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-3 text-center leading-snug shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50/50">{{ __('Programs & initiatives') }}</a>
                </li>
                <li class="min-w-0">
                    <a href="{{ route('volunteer.index', $localeQ) }}" class="flex min-h-[3.25rem] w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-3 text-center leading-snug shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50/50">{{ __('Volunteer') }}</a>
                </li>
                <li class="min-w-0">
                    <a href="{{ route('contact.show', $localeQ) }}" class="flex min-h-[3.25rem] w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-3 text-center leading-snug shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50/50">{{ __('Contact and support') }}</a>
                </li>
            </ul>
        </section>

        <section class="mx-auto mt-12 max-w-3xl rounded-2xl border border-emerald-200/80 bg-emerald-50/40 p-6 sm:p-8" aria-labelledby="youth-contact-heading">
            <h2 id="youth-contact-heading" class="font-display text-lg font-bold text-emerald-950 sm:text-xl">{{ __('Youth councils contact title') }}</h2>
            <p class="mt-3 text-sm text-slate-700 leading-relaxed">{{ __('Youth councils contact intro') }}</p>
            @if (is_string($youthEmail) && $youthEmail !== '')
                <p class="mt-5">
                    <a href="mailto:{{ $youthEmail }}" class="inline-flex items-center rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">{{ __('Email us') }}</a>
                </p>
                <p class="mt-3 text-sm font-semibold text-emerald-900">
                    <a href="mailto:{{ $youthEmail }}" class="hover:underline">{{ $youthEmail }}</a>
                </p>
            @endif
            <p class="mt-6 text-xs text-slate-600 leading-relaxed">{{ __('Youth councils contact general hint') }}</p>
        </section>

        <p class="mx-auto mt-12 max-w-3xl border-t border-slate-200 pt-10 text-center">
            <a href="{{ route('volunteer.opportunities.index', $localeQ) }}" class="text-sm font-bold text-emerald-800 hover:underline" data-testid="youth-councils-footer-opportunities">{{ __('Browse opportunities') }} →</a>
        </p>
    </article>
</x-public-layout>

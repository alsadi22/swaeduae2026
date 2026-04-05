@php
    /** @var \App\Models\CmsPage|null $cmsPage */
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\CmsPage> $programPages */
    $breadcrumbItems = [
        ['name' => __('Home'), 'url' => route('home', \App\Support\PublicLocale::query(), true)],
        ['name' => __('Programs'), 'url' => route('programs.index', \App\Support\PublicLocale::query(), true)],
    ];
    $pageTitle = $cmsPage
        ? $cmsPage->title.' — '.__('SwaedUAE')
        : __('Programs').' — '.__('SwaedUAE');
    $metaDescription = $cmsPage
        ? ($cmsPage->meta_description ?? $cmsPage->excerpt)
        : __('site.programs_meta_description');
    $pageAbsoluteUrl = $cmsPage
        ? $cmsPage->absolutePublicUrl()
        : route('programs.index', \App\Support\PublicLocale::query(), true);
    $ogTitle = $cmsPage ? $cmsPage->title : __('Programs & initiatives');
    $ogDescription = $metaDescription ?? __('site.meta_description');
    $ogImage = $cmsPage ? $cmsPage->resolvedOgImageUrl() : \App\Models\CmsPage::resolveShareImageUrl(config('swaeduae.default_og_image_url'));
@endphp
<x-public-layout
    :title="$pageTitle"
    :metaDescription="$metaDescription"
    :ogUrl="$pageAbsoluteUrl"
    :canonicalUrl="$pageAbsoluteUrl"
    :ogTitle="$ogTitle"
    :ogDescription="$ogDescription"
    :ogImage="$ogImage"
    :breadcrumbItems="$breadcrumbItems"
>
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        @if ($cmsPage)
            <header class="mx-auto max-w-3xl border-b border-slate-200 pb-8">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-800">{{ __('SwaedUAE') }}</p>
                <h1 class="public-page-title mt-3">{{ $cmsPage->title }}</h1>
                @if ($cmsPage->excerpt)
                    <p class="mt-6 text-lg text-slate-600 leading-relaxed">{{ $cmsPage->excerpt }}</p>
                @endif
            </header>
            <div class="cms-body prose prose-slate mx-auto mt-10 max-w-3xl prose-headings:font-display prose-headings:text-emerald-950 prose-a:text-emerald-800 prose-a:font-medium">
                {!! str($cmsPage->body)->markdown() !!}
            </div>
        @else
            <h1 class="public-page-title">{{ __('Programs & initiatives') }}</h1>
            <p class="mt-8 max-w-2xl text-slate-600 leading-relaxed">{{ __('site.programs_intro') }}</p>
        @endif

        <section class="@if($cmsPage) mt-16 border-t border-slate-200 pt-14 @else mt-12 @endif" aria-labelledby="programs-grid-heading">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <h2 id="programs-grid-heading" class="font-display text-xl font-bold text-emerald-950 sm:text-2xl">
                    {{ __('Featured program pages') }}
                </h2>
                <a href="{{ route('contact.show') }}" class="text-sm font-bold text-emerald-800 hover:text-emerald-950 hover:underline">
                    {{ __('Partner with us') }} →
                </a>
            </div>
            <p class="mt-3 max-w-2xl text-sm text-slate-600">{{ __('site.programs_grid_hint') }}</p>

            <form method="get" action="{{ route('programs.index') }}" class="card-surface mt-8 flex flex-col gap-4 p-4 sm:flex-row sm:flex-wrap sm:items-end">
                @foreach (\App\Support\PublicLocale::query() as $lk => $lv)
                    <input type="hidden" name="{{ $lk }}" value="{{ $lv }}">
                @endforeach
                <div class="min-w-0 flex-1">
                    <label for="public_programs_q" class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Search programs') }}</label>
                    <input type="search" id="public_programs_q" name="q" value="{{ $search }}" maxlength="120" placeholder="{{ __('Search by title or body') }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="btn-primary-solid">{{ __('Apply') }}</button>
                    @if (filled($search))
                        <a href="{{ route('programs.index', \App\Support\PublicLocale::query()) }}" class="btn-secondary-muted">{{ __('Clear') }}</a>
                    @endif
                </div>
            </form>

            @if ($programPages->total() === 0)
                @if (filled($search))
                    <p class="mt-8 text-sm text-slate-600">{{ __('No program pages match your search.') }}</p>
                @else
                    <div class="mt-10 rounded-2xl border border-dashed border-slate-300 bg-slate-50/80 p-10 text-center sm:col-span-2 lg:col-span-3">
                        <p class="text-slate-600">{{ __('site.programs_empty') }}</p>
                        <p class="mt-3 text-sm text-slate-500">{{ __('site.programs_empty_admin_hint') }}</p>
                    </div>
                @endif
            @else
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($programPages as $p)
                        <article class="card-surface group overflow-hidden transition duration-200 hover:shadow-card-hover">
                            <a href="{{ $p->publicUrl() }}?lang={{ app()->getLocale() }}" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                                <div class="h-32 bg-gradient-to-br from-emerald-100 via-slate-50 to-amber-50 transition group-hover:from-emerald-200/90"></div>
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
                @if ($programPages->hasPages())
                    <div class="mt-10">
                        {{ $programPages->links('vendor.pagination.tailwind-public') }}
                    </div>
                @endif
            @endif
        </section>
    </div>
</x-public-layout>

@php
    use App\Support\PublicBreadcrumbs;
    use App\Support\PublicLocale;

    $pageTitle = __('Gallery').' — '.__('SwaedUAE');
    $downloads = config('swaeduae.document_downloads', []);
    $localeQ = PublicLocale::queryFromRequestOrUser(auth()->user());
    $breadcrumbItems = PublicBreadcrumbs::homeAndCurrent(__('Gallery'), route('gallery', $localeQ, true));
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="__('site.gallery_meta_description')" :breadcrumbItems="$breadcrumbItems">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <div class="max-w-3xl">
            <h1 class="public-page-title">{{ __('Gallery') }}</h1>
            <p class="mt-8 text-slate-600 leading-relaxed">{{ __('site.gallery_page_intro') }}</p>
            <p class="mt-4">
                <a href="{{ route('media.index', $localeQ) }}" class="text-sm font-bold text-emerald-800 hover:underline">{{ __('Media center') }} →</a>
            </p>
        </div>

        <div class="mt-12 rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center text-slate-600">
            {{ __('site.gallery_placeholder') }}
        </div>

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

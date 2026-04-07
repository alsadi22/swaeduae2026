@php
    use App\Support\PublicBreadcrumbs;
    use App\Support\PublicLocale;
    use App\Support\SwaedUaeStructuredData;

    $pageTitle = __('FAQ').' — '.__('SwaedUAE');
    $localeQ = PublicLocale::queryFromRequestOrUser(auth()->user());
    $breadcrumbItems = PublicBreadcrumbs::homeAndCurrent(__('FAQ'), route('faq', $localeQ, true));
    $faqItemsRaw = trans('site.faq_items');
    /** @var list<array{question: string, answer: string}> $faqItems */
    $faqItems = is_array($faqItemsRaw) ? $faqItemsRaw : [];
    $faqCanonical = route('faq', $localeQ, true);
    $ogImage = \App\Models\CmsPage::resolveShareImageUrl(config('swaeduae.default_og_image_url'));
    $extraJsonLd = SwaedUaeStructuredData::faqPageFromItems($faqItems);
@endphp
<x-public-layout
    :title="$pageTitle"
    :metaDescription="__('site.faq_meta_description')"
    :ogUrl="$faqCanonical"
    :canonicalUrl="$faqCanonical"
    :ogTitle="__('FAQ')"
    :ogDescription="__('site.faq_meta_description')"
    :ogImage="$ogImage"
    :breadcrumbItems="$breadcrumbItems"
    :extraJsonLd="$extraJsonLd"
>
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <div class="max-w-3xl">
            <h1 class="public-page-title">{{ __('FAQ') }}</h1>
            <p class="mt-8 text-slate-600 leading-relaxed">{{ __('site.faq_intro') }}</p>
            <div class="mt-6">
                <x-copy-filtered-list-url-button class="max-sm:[&_button]:w-full" test-id="faq-copy-page-url" />
            </div>
            <div class="mt-10 space-y-4">
                @foreach ($faqItems as $i => $item)
                    @php
                        $q = $item['question'] ?? '';
                        $a = $item['answer'] ?? '';
                    @endphp
                    @if (filled($q) && filled($a))
                        <details
                            class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm open:ring-2 open:ring-emerald-100"
                            @if ($i === 0) open @endif
                        >
                            <summary class="cursor-pointer font-display font-semibold text-slate-900">{{ $q }}</summary>
                            <p class="mt-3 text-sm text-slate-600 leading-relaxed">{{ $a }}</p>
                        </details>
                    @endif
                @endforeach
            </div>
            <p class="mt-12 border-t border-slate-200 pt-10 text-center">
                <a href="{{ route('volunteer.opportunities.index', $localeQ) }}" class="text-sm font-bold text-emerald-800 hover:underline" data-testid="faq-footer-opportunities">{{ __('Browse opportunities') }} →</a>
            </p>
        </div>
    </div>
</x-public-layout>

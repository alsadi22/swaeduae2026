@php
    $pageTitle = __('Volunteer platform').' — '.config('app.name');
    $metaDescription = __('site.volunteer_highlight');
    $localeQ = \App\Support\PublicLocale::query();
    $breadcrumbItems = [
        ['name' => __('Home'), 'url' => route('home', $localeQ, true)],
        ['name' => __('Volunteer platform'), 'url' => route('volunteer.index', $localeQ, true)],
    ];
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="$metaDescription" :breadcrumbItems="$breadcrumbItems">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <div class="card-surface p-8 sm:p-10">
            <h1 class="public-page-title">{{ __('Volunteer platform') }}</h1>
            <p class="mt-8 text-slate-600 leading-relaxed">
                {{ __('Volunteer attendance hint') }}
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('volunteer.opportunities.index', $localeQ) }}" class="btn-primary-solid">{{ __('Browse opportunities') }}</a>
                @guest
                    @php($registerQ = array_merge(\App\Support\IntendedUrl::queryParamsForRequestUri(request()), $localeQ))
                    @if (Route::has('register.volunteer'))
                        <a href="{{ route('register.volunteer', $registerQ) }}" class="btn-secondary-muted">{{ __('Create account') }}</a>
                    @elseif (Route::has('register'))
                        <a href="{{ route('register', $registerQ) }}" class="btn-secondary-muted">{{ __('Create account') }}</a>
                    @endif
                @endguest
            </div>
            <p class="mt-10">
                <a href="{{ route('home', $localeQ) }}" class="footer-link text-sm font-semibold">{{ __('Back to home') }}</a>
            </p>
        </div>
    </div>
</x-public-layout>

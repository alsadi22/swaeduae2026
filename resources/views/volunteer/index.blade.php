@php
    $pageTitle = __('Volunteer platform').' — '.config('app.name');
    $metaDescription = __('site.volunteer_highlight');
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="$metaDescription">
    <div class="mx-auto max-w-content px-4 py-12 sm:px-6 sm:py-16">
        <div class="card-surface p-8 sm:p-10">
            <h1 class="public-page-title">{{ __('Volunteer platform') }}</h1>
            <p class="mt-8 text-slate-600 leading-relaxed">
                {{ __('Volunteer attendance hint') }}
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('volunteer.opportunities.index') }}" class="btn-primary-solid">{{ __('Browse opportunities') }}</a>
                @guest
                    @if (Route::has('register.volunteer'))
                        <a href="{{ route('register.volunteer', \App\Support\IntendedUrl::queryParamsForRequestUri(request())) }}" class="btn-secondary-muted">{{ __('Create account') }}</a>
                    @elseif (Route::has('register'))
                        <a href="{{ route('register', \App\Support\IntendedUrl::queryParamsForRequestUri(request())) }}" class="btn-secondary-muted">{{ __('Create account') }}</a>
                    @endif
                @endguest
            </div>
            <p class="mt-10">
                <a href="{{ route('home') }}" class="footer-link text-sm font-semibold">{{ __('Back to home') }}</a>
            </p>
        </div>
    </div>
</x-public-layout>

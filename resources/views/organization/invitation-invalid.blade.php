@php
    $pageTitle = __('Invalid invitation').' — '.config('app.name');
@endphp
<x-public-layout :title="$pageTitle" :metaDescription="$pageTitle">
    <div class="mx-auto max-w-content px-4 py-16 sm:px-6">
        <div class="card-surface mx-auto max-w-lg p-8 text-center">
            <h1 class="font-display text-xl font-bold text-slate-900">{{ __('Invalid or expired invitation') }}</h1>
            <p class="mt-4 text-sm text-slate-600">{{ __('Organization invitation invalid hint') }}</p>
            <a href="{{ route('home', \App\Support\PublicLocale::query()) }}" class="mt-6 inline-flex text-sm font-bold text-emerald-800 hover:underline">{{ __('Back to home') }}</a>
        </div>
    </div>
</x-public-layout>

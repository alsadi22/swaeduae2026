@php
    $appShellTitle = __('Edit CMS page').' — '.__('SwaedUAE');
@endphp
<x-admin-layout :title="$appShellTitle" :meta-description="__('site.meta_description')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit CMS page') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex flex-wrap items-center justify-end gap-2 border-b border-gray-100 px-6 py-3">
                    <x-copy-filtered-list-url-button class="[&_button]:border-gray-300 [&_button]:text-gray-700" test-id="admin-cms-page-edit-copy-page-url" />
                </div>
                <form method="post" action="{{ route('admin.cms-pages.update', array_merge(['cms_page' => $page], $adminLocaleQ)) }}" enctype="multipart/form-data" class="p-6 space-y-6">
                    @csrf
                    @method('put')
                    @include('admin.cms-pages._form', ['page' => $page])

                    <div class="flex flex-wrap items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('admin.cms-pages.index', $adminLocaleQ) }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                        <a href="{{ route('admin.cms-pages.preview', array_merge(['cms_page' => $page], $adminLocaleQ, ['lang' => $page->locale])) }}" target="_blank" rel="noopener" class="text-sm text-indigo-600 hover:text-indigo-900">{{ __('Preview') }}</a>
                        @if ($page->exists && $page->isPubliclyVisible())
                            <a href="{{ $page->publicUrl() }}?lang={{ $page->locale }}" target="_blank" rel="noopener" class="text-sm text-emerald-700 hover:text-emerald-900">{{ __('View on site') }}</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit CMS page') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="post" action="{{ route('admin.cms-pages.update', array_merge(['cms_page' => $page], $adminLocaleQ)) }}" class="p-6 space-y-6">
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
</x-app-layout>

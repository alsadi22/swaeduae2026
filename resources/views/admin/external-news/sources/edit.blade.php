<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit news source') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="post" action="{{ route('admin.external-news-sources.update', array_merge(['external_news_source' => $source], $adminLocaleQ)) }}" class="space-y-6">
                    @csrf
                    @method('put')
                    @include('admin.external-news.sources._form', ['source' => $source])
                    <div class="flex flex-wrap gap-3">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('admin.external-news-sources.index', $adminLocaleQ) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
            <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-red-100">
                <h3 class="text-sm font-semibold text-red-800">{{ __('Danger zone') }}</h3>
                <form method="post" action="{{ route('admin.external-news-sources.destroy', array_merge(['external_news_source' => $source], $adminLocaleQ)) }}" class="mt-4" onsubmit="return confirm(@json(__('Delete this source and its fetch logs? Imported items will be removed.')));">
                    @csrf
                    @method('delete')
                    <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-xs font-semibold text-white hover:bg-red-700">{{ __('Delete source') }}</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

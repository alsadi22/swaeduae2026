<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add news source') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="post" action="{{ route('admin.external-news-sources.store') }}" class="space-y-6">
                    @csrf
                    @include('admin.external-news.sources._form', ['source' => $source])
                    <div class="flex gap-3">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('admin.external-news-sources.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

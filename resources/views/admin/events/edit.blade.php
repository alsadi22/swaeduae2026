@php
    $appShellTitle = __('Edit event').' — '.__('SwaedUAE');
@endphp
<x-admin-layout :title="$appShellTitle" :meta-description="__('site.meta_description')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex flex-wrap items-center justify-end gap-2 border-b border-gray-100 px-6 py-3">
                    <x-copy-filtered-list-url-button class="[&_button]:border-gray-300 [&_button]:text-gray-700" test-id="admin-event-edit-copy-page-url" />
                </div>
                <form method="post" action="{{ route('admin.events.update', array_merge(['event' => $event], $adminLocaleQ)) }}" class="p-6 space-y-6">
                    @csrf
                    @method('put')
                    @include('admin.events._form', ['event' => $event, 'organizations' => $organizations])

                    <div class="flex flex-wrap items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('admin.events.index', $adminLocaleQ) }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>

                @include('admin.events._checkpoint_link', ['event' => $event])
            </div>
        </div>
    </div>
</x-admin-layout>

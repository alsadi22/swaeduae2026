<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="post" action="{{ route('admin.events.update', $event) }}" class="p-6 space-y-6">
                    @csrf
                    @method('put')
                    @include('admin.events._form', ['event' => $event, 'organizations' => $organizations])

                    <div class="flex flex-wrap items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('admin.events.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>

                @include('admin.events._checkpoint_link', ['event' => $event])
            </div>
        </div>
    </div>
</x-app-layout>

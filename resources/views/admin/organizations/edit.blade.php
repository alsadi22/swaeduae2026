<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit organization') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="post" action="{{ route('admin.organizations.update', array_merge(['organization' => $organization], $adminLocaleQ)) }}" class="p-6 space-y-6">
                    @csrf
                    @method('put')
                    @include('admin.organizations._form', ['organization' => $organization])

                    <div class="flex flex-wrap items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('admin.organizations.index', $adminLocaleQ) }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

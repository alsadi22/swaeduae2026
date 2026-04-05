<x-app-layout>
    @php
        $orgLocaleQ = \App\Support\PublicLocale::query();
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Organization portal new event title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="post" action="{{ route('organization.events.store', $orgLocaleQ) }}" class="p-6 space-y-6">
                    @csrf
                    @include('admin.events._form', [
                        'event' => $event,
                        'organizations' => collect(),
                        'organizationPortal' => true,
                        'portalOrganization' => $portalOrganization,
                    ])

                    <div class="flex flex-wrap items-center gap-4">
                        <x-primary-button>{{ __('Create') }}</x-primary-button>
                        <a href="{{ route('organization.events.index', $orgLocaleQ) }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

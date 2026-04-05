<x-app-layout>
    @php
        $orgLocaleQ = \App\Support\PublicLocale::query();
    @endphp
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Organization portal edit event title') }}
            </h2>
            <a href="{{ route('organization.events.roster', array_merge(['event' => $event], $orgLocaleQ)) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('View roster') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="post" action="{{ route('organization.events.update', array_merge(['event' => $event], $orgLocaleQ)) }}" class="p-6 space-y-6">
                    @csrf
                    @method('put')
                    @include('admin.events._form', [
                        'event' => $event,
                        'organizations' => collect(),
                        'organizationPortal' => true,
                        'portalOrganization' => $portalOrganization,
                    ])

                    <div class="flex flex-wrap items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('organization.events.index', $orgLocaleQ) }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>

                @include('admin.events._checkpoint_link', [
                    'event' => $event,
                    'checkpointSignedUrlRoute' => 'organization.events.checkpoint-signed-url',
                    'checkpointRouteQuery' => $orgLocaleQ,
                ])
            </div>
        </div>
    </div>
</x-app-layout>

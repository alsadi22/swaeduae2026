<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New CMS page') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="post" action="{{ route('admin.cms-pages.store') }}" class="p-6 space-y-6">
                    @csrf
                    @include('admin.cms-pages._form', ['page' => $page])

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Create') }}</x-primary-button>
                        <a href="{{ route('admin.cms-pages.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

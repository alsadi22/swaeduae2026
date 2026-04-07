@php
    $appShellTitle = __('Edit organization').' — '.__('SwaedUAE');
@endphp
<x-admin-layout :title="$appShellTitle" :meta-description="__('site.meta_description')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit organization') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex flex-wrap items-center justify-end gap-2 border-b border-gray-100 px-6 py-3">
                    <x-copy-filtered-list-url-button class="[&_button]:border-gray-300 [&_button]:text-gray-700" test-id="admin-organization-edit-copy-page-url" />
                </div>
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

            @if ($organizationDocuments->isNotEmpty())
                <div class="mt-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Organization documents title') }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Admin organization documents hint') }}</p>
                    </div>
                    <ul class="divide-y divide-gray-100 px-6 py-2 text-sm" data-testid="admin-organization-documents-list">
                        @foreach ($organizationDocuments as $doc)
                            <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $doc->original_filename }}</p>
                                    <p class="text-xs text-gray-500">{{ $doc->created_at->format('Y-m-d H:i') }} · {{ $doc->uploadedByUser?->email ?? '—' }}</p>
                                </div>
                                <a
                                    href="{{ route('admin.organizations.documents.download', array_merge(['organization' => $organization, 'organization_document' => $doc], $adminLocaleQ)) }}"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-900"
                                    data-testid="admin-organization-document-download"
                                >{{ __('Download') }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>

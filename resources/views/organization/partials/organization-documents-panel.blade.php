@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\OrganizationDocument> $organizationDocuments */
    $orgLocaleQ = $orgLocaleQ ?? \App\Support\PublicLocale::queryFromRequestOrUser(auth()->user());
    $maxDocs = \App\Models\OrganizationDocument::MAX_FILES_PER_ORGANIZATION;
@endphp
<div class="overflow-hidden border border-slate-200 bg-white p-6 shadow-sm sm:rounded-lg sm:p-8" data-testid="organization-documents-panel">
    <h3 class="font-display text-lg font-bold text-slate-900">{{ __('Organization documents title') }}</h3>
    <p class="mt-1 text-sm text-slate-600">{{ __('Organization documents hint', ['max' => (string) $maxDocs]) }}</p>

    @if ($organizationDocuments->isNotEmpty())
        <ul class="mt-4 divide-y divide-slate-100 text-sm" data-testid="organization-documents-list">
            @foreach ($organizationDocuments as $doc)
                <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                    <div>
                        <p class="font-medium text-slate-900">{{ $doc->original_filename }}</p>
                        <p class="text-xs text-slate-500">
                            {{ __('Uploaded') }} {{ $doc->created_at->locale(app()->getLocale())->isoFormat('LLL') }}
                            @if ($doc->uploadedByUser)
                                · {{ $doc->uploadedByUser->name }}
                            @endif
                        </p>
                    </div>
                    @can('delete', $doc)
                        <form action="{{ route('organization.documents.destroy', array_merge(['organization_document' => $doc], $orgLocaleQ)) }}" method="post" onsubmit="return confirm(@json(__('Remove this file?')));">
                            @csrf
                            @method('delete')
                            <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800" data-testid="organization-document-delete">{{ __('Remove') }}</button>
                        </form>
                    @endcan
                </li>
            @endforeach
        </ul>
    @else
        <p class="mt-4 text-sm text-slate-600" data-testid="organization-documents-empty">{{ __('Organization documents empty') }}</p>
    @endif

    @if ($organizationDocuments->count() < $maxDocs)
        <form action="{{ route('organization.documents.store', $orgLocaleQ) }}" method="post" enctype="multipart/form-data" class="mt-6 space-y-3 border-t border-slate-100 pt-6">
            @csrf
            <div>
                <x-input-label for="org_document_upload" :value="__('Organization documents upload label')" />
                <input id="org_document_upload" name="document" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="mt-1 block w-full text-sm text-slate-600" data-testid="organization-document-file-input" />
                <x-input-error class="mt-2" :messages="$errors->get('document')" />
            </div>
            <x-primary-button type="submit" data-testid="organization-document-upload-submit">{{ __('Upload') }}</x-primary-button>
        </form>
    @endif
</div>

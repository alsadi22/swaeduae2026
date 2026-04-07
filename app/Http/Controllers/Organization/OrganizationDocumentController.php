<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationDocumentRequest;
use App\Models\OrganizationDocument;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationDocumentController extends Controller
{
    public function store(StoreOrganizationDocumentRequest $request): RedirectResponse
    {
        $organization = $request->user()->organization;
        if ($organization === null) {
            abort(403);
        }

        $file = $request->file('document');
        if ($file === null) {
            abort(422);
        }

        $path = $file->store('organization_documents/'.$organization->id, 'local');

        OrganizationDocument::query()->create([
            'organization_id' => $organization->id,
            'uploaded_by_user_id' => $request->user()->id,
            'disk' => 'local',
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => (string) ($file->getMimeType() ?? 'application/octet-stream'),
            'size_bytes' => $file->getSize(),
        ]);

        $localeQ = PublicLocale::queryFromRequestOrUser($request->user());

        return redirect()
            ->route('organization.dashboard', $localeQ)
            ->with('status', __('Organization document uploaded.'));
    }

    public function destroy(Request $request, OrganizationDocument $organization_document): RedirectResponse
    {
        $this->authorize('delete', $organization_document);

        Storage::disk($organization_document->disk)->delete($organization_document->path);
        $organization_document->delete();

        $localeQ = PublicLocale::queryFromRequestOrUser($request->user());

        return redirect()
            ->route('organization.dashboard', $localeQ)
            ->with('status', __('Organization document removed.'));
    }
}

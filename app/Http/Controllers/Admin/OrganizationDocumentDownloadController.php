<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrganizationDocumentDownloadController extends Controller
{
    public function __invoke(Request $request, Organization $organization, OrganizationDocument $organization_document): StreamedResponse
    {
        $this->authorize('update', $organization);

        if ($organization_document->organization_id !== $organization->id) {
            abort(404);
        }

        if (! Storage::disk($organization_document->disk)->exists($organization_document->path)) {
            abort(404);
        }

        return Storage::disk($organization_document->disk)->download(
            $organization_document->path,
            $organization_document->original_filename,
            ['Content-Type' => $organization_document->mime_type]
        );
    }
}

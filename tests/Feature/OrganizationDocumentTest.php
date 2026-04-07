<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationDocument;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrganizationDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_org_owner_can_upload_pdf_while_organization_is_pending(): void
    {
        Storage::fake('local');

        $this->seed(RoleSeeder::class);
        $org = Organization::factory()->pendingVerification()->create();
        $owner = User::factory()->create();
        $owner->forceFill(['organization_id' => $org->id])->save();
        $owner->assignRole('org-owner');

        $file = UploadedFile::fake()->create('charter.pdf', 100, 'application/pdf');

        $this->actingAs($owner)
            ->post(route('organization.documents.store'), ['document' => $file])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('organization_documents', [
            'organization_id' => $org->id,
            'uploaded_by_user_id' => $owner->id,
            'original_filename' => 'charter.pdf',
        ]);

        $doc = OrganizationDocument::query()->firstOrFail();
        Storage::disk('local')->assertExists($doc->path);
    }

    public function test_org_coordinator_cannot_upload_documents(): void
    {
        Storage::fake('local');

        $this->seed(RoleSeeder::class);
        $org = Organization::factory()->create();
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $org->id])->save();
        $user->assignRole('org-coordinator');

        $file = UploadedFile::fake()->create('x.pdf', 50, 'application/pdf');

        $this->actingAs($user)
            ->post(route('organization.documents.store'), ['document' => $file])
            ->assertForbidden();
    }

    public function test_rejected_organization_cannot_upload_documents(): void
    {
        Storage::fake('local');

        $this->seed(RoleSeeder::class);
        $org = Organization::factory()->create(['verification_status' => Organization::VERIFICATION_REJECTED]);
        $owner = User::factory()->create();
        $owner->forceFill(['organization_id' => $org->id])->save();
        $owner->assignRole('org-owner');

        $file = UploadedFile::fake()->create('x.pdf', 50, 'application/pdf');

        $this->actingAs($owner)
            ->post(route('organization.documents.store'), ['document' => $file])
            ->assertForbidden();
    }

    public function test_org_manager_can_delete_document_from_own_organization(): void
    {
        Storage::fake('local');

        $this->seed(RoleSeeder::class);
        $org = Organization::factory()->create();
        $manager = User::factory()->create();
        $manager->forceFill(['organization_id' => $org->id])->save();
        $manager->assignRole('org-manager');

        $path = 'organization_documents/'.$org->id.'/test.pdf';
        Storage::disk('local')->put($path, 'fake-pdf');

        $doc = OrganizationDocument::query()->create([
            'organization_id' => $org->id,
            'uploaded_by_user_id' => $manager->id,
            'disk' => 'local',
            'path' => $path,
            'original_filename' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 8,
        ]);

        $this->actingAs($manager)
            ->delete(route('organization.documents.destroy', ['organization_document' => $doc]))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('organization_documents', ['id' => $doc->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_tenth_upload_then_eleventh_fails_validation(): void
    {
        Storage::fake('local');

        $this->seed(RoleSeeder::class);
        $org = Organization::factory()->create();
        $owner = User::factory()->create();
        $owner->forceFill(['organization_id' => $org->id])->save();
        $owner->assignRole('org-owner');

        for ($i = 0; $i < OrganizationDocument::MAX_FILES_PER_ORGANIZATION; $i++) {
            $file = UploadedFile::fake()->create("doc{$i}.pdf", 20, 'application/pdf');
            $this->actingAs($owner)
                ->post(route('organization.documents.store'), ['document' => $file])
                ->assertSessionHasNoErrors();
        }

        $file = UploadedFile::fake()->create('extra.pdf', 20, 'application/pdf');
        $this->actingAs($owner)
            ->post(route('organization.documents.store'), ['document' => $file])
            ->assertSessionHasErrors('document');

        $this->assertSame(OrganizationDocument::MAX_FILES_PER_ORGANIZATION, OrganizationDocument::query()->where('organization_id', $org->id)->count());
    }

    public function test_admin_can_download_organization_document(): void
    {
        Storage::fake('local');

        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $org = Organization::factory()->create();
        $uploader = User::factory()->create();

        $path = 'organization_documents/'.$org->id.'/policy.pdf';
        Storage::disk('local')->put($path, 'pdf-binary-content');

        $doc = OrganizationDocument::query()->create([
            'organization_id' => $org->id,
            'uploaded_by_user_id' => $uploader->id,
            'disk' => 'local',
            'path' => $path,
            'original_filename' => 'policy.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => strlen('pdf-binary-content'),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.organizations.documents.download', ['organization' => $org, 'organization_document' => $doc]))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=policy.pdf');
    }

    public function test_get_organization_documents_redirects_to_dashboard_with_locale(): void
    {
        $this->seed(RoleSeeder::class);
        $org = Organization::factory()->create();
        $owner = User::factory()->create();
        $owner->forceFill(['organization_id' => $org->id])->save();
        $owner->assignRole('org-owner');

        $this->actingAs($owner)
            ->get('/organization/documents?lang=en')
            ->assertRedirect(route('organization.dashboard', ['lang' => 'en'], false));
    }

    public function test_pending_dashboard_shows_documents_panel(): void
    {
        $this->seed(RoleSeeder::class);
        $org = Organization::factory()->pendingVerification()->create();
        $owner = User::factory()->create();
        $owner->forceFill(['organization_id' => $org->id])->save();
        $owner->assignRole('org-owner');

        $this->actingAs($owner)
            ->get(route('organization.dashboard'))
            ->assertOk()
            ->assertSee('data-testid="organization-documents-panel"', false);
    }
}

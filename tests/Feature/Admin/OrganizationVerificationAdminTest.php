<?php

namespace Tests\Feature\Admin;

use App\Mail\OrganizationVerificationMail;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrganizationVerificationAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_admin_can_approve_pending_organization(): void
    {
        $admin = $this->admin();
        $org = Organization::factory()->pendingVerification()->create(['name_en' => 'Pending Org']);

        $this->actingAs($admin)
            ->post(route('admin.organizations.approve', $org))
            ->assertRedirect(route('admin.organizations.index', ['verification' => 'pending'], false));

        $this->assertTrue($org->fresh()->isApproved());
        $this->assertNotNull($org->fresh()->verification_reviewed_at);
    }

    public function test_admin_can_reject_pending_organization_with_note(): void
    {
        $admin = $this->admin();
        $org = Organization::factory()->pendingVerification()->create(['name_en' => 'Reject Me']);

        $this->actingAs($admin)
            ->post(route('admin.organizations.reject', $org), ['review_note' => 'Incomplete details.'])
            ->assertRedirect(route('admin.organizations.index', ['verification' => 'pending'], false));

        $org->refresh();
        $this->assertTrue($org->isRejected());
        $this->assertSame('Incomplete details.', $org->verification_review_note);
    }

    public function test_admin_cannot_approve_non_pending_organization(): void
    {
        $admin = $this->admin();
        $org = Organization::factory()->create(['name_en' => 'Already ok']);

        $this->actingAs($admin)
            ->post(route('admin.organizations.approve', $org))
            ->assertForbidden();
    }

    public function test_volunteer_cannot_approve_organization(): void
    {
        $this->seed(RoleSeeder::class);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $org = Organization::factory()->pendingVerification()->create();

        $this->actingAs($volunteer)
            ->post(route('admin.organizations.approve', $org))
            ->assertForbidden();
    }

    public function test_volunteer_cannot_reject_organization(): void
    {
        $this->seed(RoleSeeder::class);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $org = Organization::factory()->pendingVerification()->create();

        $this->actingAs($volunteer)
            ->post(route('admin.organizations.reject', $org), ['review_note' => 'No access.'])
            ->assertForbidden();
    }

    public function test_approval_queues_email_to_registering_user(): void
    {
        Mail::fake();

        $admin = $this->admin();
        $registrant = User::factory()->create(['email' => 'owner@example.org']);
        $org = Organization::factory()->pendingVerification()->create([
            'name_en' => 'Mail Org',
            'registered_by_user_id' => $registrant->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.organizations.approve', $org))
            ->assertRedirect(route('admin.organizations.index', ['verification' => 'pending'], false));

        Mail::assertQueued(OrganizationVerificationMail::class, function (OrganizationVerificationMail $mail) use ($registrant, $org): bool {
            return $mail->approved === true
                && $mail->organization->is($org->fresh())
                && $mail->recipient->is($registrant)
                && $mail->hasTo($registrant->email);
        });
    }

    public function test_rejection_queues_email_with_note_to_registering_user(): void
    {
        Mail::fake();

        $admin = $this->admin();
        $registrant = User::factory()->create(['email' => 'owner2@example.org']);
        $org = Organization::factory()->pendingVerification()->create([
            'name_en' => 'Reject Mail Org',
            'registered_by_user_id' => $registrant->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.organizations.reject', $org), ['review_note' => 'Incomplete details.'])
            ->assertRedirect(route('admin.organizations.index', ['verification' => 'pending'], false));

        $org->refresh();
        Mail::assertQueued(OrganizationVerificationMail::class, function (OrganizationVerificationMail $mail) use ($registrant, $org): bool {
            return $mail->approved === false
                && $mail->organization->is($org)
                && $mail->recipient->is($registrant)
                && $mail->organization->verification_review_note === 'Incomplete details.';
        });
    }

    public function test_approval_does_not_queue_email_when_no_registering_user(): void
    {
        Mail::fake();

        $admin = $this->admin();
        $org = Organization::factory()->pendingVerification()->create([
            'name_en' => 'No Registrant Org',
            'registered_by_user_id' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.organizations.approve', $org))
            ->assertRedirect(route('admin.organizations.index', ['verification' => 'pending'], false));

        Mail::assertNothingQueued();
    }
}

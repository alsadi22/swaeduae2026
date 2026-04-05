<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use App\Notifications\OrganizationStaffInvitation;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrganizationInvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    private function approvedOrgWithOwner(): array
    {
        $this->seed(RoleSeeder::class);
        $organization = Organization::factory()->create();
        $owner = User::factory()->create();
        $owner->forceFill(['organization_id' => $organization->id])->save();
        $owner->assignRole('org-owner');

        return [$organization, $owner];
    }

    public function test_org_owner_can_send_staff_invitation(): void
    {
        Notification::fake();

        [$organization, $owner] = $this->approvedOrgWithOwner();

        $this->actingAs($owner)->post(route('organization.invitations.store'), [
            'email' => 'staff@example.org',
            'role' => OrganizationInvitation::ROLE_MANAGER,
        ])->assertRedirect(route('organization.dashboard', [], false));

        $this->assertDatabaseHas('organization_invitations', [
            'organization_id' => $organization->id,
            'email' => 'staff@example.org',
            'role' => OrganizationInvitation::ROLE_MANAGER,
        ]);

        Notification::assertSentOnDemand(OrganizationStaffInvitation::class);
    }

    public function test_org_manager_cannot_send_invitation(): void
    {
        $this->seed(RoleSeeder::class);
        $organization = Organization::factory()->create();
        $manager = User::factory()->create();
        $manager->forceFill(['organization_id' => $organization->id])->save();
        $manager->assignRole('org-manager');

        $this->actingAs($manager)->post(route('organization.invitations.store'), [
            'email' => 'x@y.com',
            'role' => OrganizationInvitation::ROLE_VIEWER,
        ])->assertForbidden();
    }

    public function test_guest_with_valid_token_redirected_to_login(): void
    {
        [$organization] = $this->approvedOrgWithOwner();
        $plain = Str::random(64);
        OrganizationInvitation::query()->create([
            'organization_id' => $organization->id,
            'email' => 'joiner@example.org',
            'role' => OrganizationInvitation::ROLE_COORDINATOR,
            'token_hash' => OrganizationInvitation::hashToken($plain),
            'invited_by_user_id' => null,
            'expires_at' => now()->addWeek(),
        ]);

        $this->get('/organization/join/'.$plain)->assertRedirect(route('login', [], false));
    }

    public function test_invalid_token_shows_public_message_without_login(): void
    {
        $this->get('/organization/join/'.Str::random(64))
            ->assertOk()
            ->assertSee(__('Invalid or expired invitation'), false);
    }

    public function test_invited_user_accepts_and_joins_organization(): void
    {
        [$organization, $owner] = $this->approvedOrgWithOwner();

        $plain = Str::random(64);
        OrganizationInvitation::query()->create([
            'organization_id' => $organization->id,
            'email' => 'joiner@example.org',
            'role' => OrganizationInvitation::ROLE_VIEWER,
            'token_hash' => OrganizationInvitation::hashToken($plain),
            'invited_by_user_id' => $owner->id,
            'expires_at' => now()->addWeek(),
        ]);

        $joiner = User::factory()->create(['email' => 'joiner@example.org']);
        $joiner->assignRole('volunteer');

        $this->actingAs($joiner)->get('/organization/join/'.$plain)
            ->assertRedirect(route('organization.dashboard', [], false));

        $joiner->refresh();
        $this->assertSame($organization->id, $joiner->organization_id);
        $this->assertTrue($joiner->hasRole('org-viewer'));

        $this->assertNotNull(
            OrganizationInvitation::query()
                ->where('organization_id', $organization->id)
                ->where('email', 'joiner@example.org')
                ->value('accepted_at')
        );
    }

    public function test_wrong_logged_in_email_sees_wrong_account_page(): void
    {
        [$organization, $owner] = $this->approvedOrgWithOwner();
        $plain = Str::random(64);
        OrganizationInvitation::query()->create([
            'organization_id' => $organization->id,
            'email' => 'joiner@example.org',
            'role' => OrganizationInvitation::ROLE_VIEWER,
            'token_hash' => OrganizationInvitation::hashToken($plain),
            'invited_by_user_id' => $owner->id,
            'expires_at' => now()->addWeek(),
        ]);

        $other = User::factory()->create(['email' => 'other@example.org']);
        $other->assignRole('volunteer');

        $this->actingAs($other)->get('/organization/join/'.$plain)
            ->assertOk()
            ->assertSee(__('Wrong account'), false);
    }

    public function test_cannot_invite_email_already_in_organization(): void
    {
        [$organization, $owner] = $this->approvedOrgWithOwner();

        $member = User::factory()->create(['email' => 'member@example.org']);
        $member->forceFill(['organization_id' => $organization->id])->save();
        $member->assignRole('org-coordinator');

        $this->actingAs($owner)->post(route('organization.invitations.store'), [
            'email' => 'member@example.org',
            'role' => OrganizationInvitation::ROLE_MANAGER,
        ])->assertSessionHasErrors('email');
    }

    public function test_owner_can_cancel_pending_invitation(): void
    {
        [$organization, $owner] = $this->approvedOrgWithOwner();

        $invitation = OrganizationInvitation::query()->create([
            'organization_id' => $organization->id,
            'email' => 'pending@example.org',
            'role' => OrganizationInvitation::ROLE_VIEWER,
            'token_hash' => OrganizationInvitation::hashToken(Str::random(64)),
            'invited_by_user_id' => $owner->id,
            'expires_at' => now()->addWeek(),
        ]);

        $this->actingAs($owner)->delete(route('organization.invitations.destroy', $invitation))
            ->assertRedirect(route('organization.dashboard', [], false));

        $this->assertDatabaseMissing('organization_invitations', ['id' => $invitation->id]);
    }

    public function test_owner_can_resend_invitation_with_new_token(): void
    {
        Notification::fake();

        [$organization, $owner] = $this->approvedOrgWithOwner();

        $oldPlain = Str::random(64);
        $invitation = OrganizationInvitation::query()->create([
            'organization_id' => $organization->id,
            'email' => 'resend@example.org',
            'role' => OrganizationInvitation::ROLE_VIEWER,
            'token_hash' => OrganizationInvitation::hashToken($oldPlain),
            'invited_by_user_id' => $owner->id,
            'expires_at' => now()->addWeek(),
        ]);
        $oldHash = $invitation->token_hash;

        $this->actingAs($owner)->post(route('organization.invitations.resend', $invitation))
            ->assertRedirect(route('organization.dashboard', [], false));

        $invitation->refresh();
        $this->assertNotSame($oldHash, $invitation->token_hash);
        $this->assertTrue($invitation->expires_at->isFuture());

        Notification::assertSentOnDemand(OrganizationStaffInvitation::class);

        $this->get('/organization/join/'.$oldPlain)
            ->assertOk()
            ->assertSee(__('Invalid or expired invitation'), false);
    }

    public function test_org_manager_cannot_resend_invitation(): void
    {
        $this->seed(RoleSeeder::class);
        $organization = Organization::factory()->create();
        $owner = User::factory()->create();
        $owner->forceFill(['organization_id' => $organization->id])->save();
        $owner->assignRole('org-owner');

        $manager = User::factory()->create();
        $manager->forceFill(['organization_id' => $organization->id])->save();
        $manager->assignRole('org-manager');

        $invitation = OrganizationInvitation::query()->create([
            'organization_id' => $organization->id,
            'email' => 'x@y.org',
            'role' => OrganizationInvitation::ROLE_VIEWER,
            'token_hash' => OrganizationInvitation::hashToken(Str::random(64)),
            'invited_by_user_id' => $owner->id,
            'expires_at' => now()->addWeek(),
        ]);

        $this->actingAs($manager)->post(route('organization.invitations.resend', $invitation))
            ->assertForbidden();
    }

    public function test_resend_makes_expired_invitation_valid_again(): void
    {
        Notification::fake();

        [$organization, $owner] = $this->approvedOrgWithOwner();

        $invitation = OrganizationInvitation::query()->create([
            'organization_id' => $organization->id,
            'email' => 'expired@example.org',
            'role' => OrganizationInvitation::ROLE_COORDINATOR,
            'token_hash' => OrganizationInvitation::hashToken(Str::random(64)),
            'invited_by_user_id' => $owner->id,
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($owner)->post(route('organization.invitations.resend', $invitation))
            ->assertRedirect(route('organization.dashboard', [], false));

        $invitation->refresh();
        $this->assertTrue($invitation->expires_at->isFuture());
    }

    public function test_invitation_resend_is_throttled(): void
    {
        Notification::fake();

        [$organization, $owner] = $this->approvedOrgWithOwner();

        $invitation = OrganizationInvitation::query()->create([
            'organization_id' => $organization->id,
            'email' => 'throttle-resend@example.org',
            'role' => OrganizationInvitation::ROLE_VIEWER,
            'token_hash' => OrganizationInvitation::hashToken(Str::random(64)),
            'invited_by_user_id' => $owner->id,
            'expires_at' => now()->addWeek(),
        ]);

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($owner)->post(route('organization.invitations.resend', $invitation))->assertRedirect(route('organization.dashboard', [], false));
        }

        $this->actingAs($owner)->post(route('organization.invitations.resend', $invitation))->assertStatus(429);
    }

    public function test_invitation_store_is_throttled(): void
    {
        Notification::fake();

        [$organization, $owner] = $this->approvedOrgWithOwner();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($owner)->post(route('organization.invitations.store'), [
                'email' => "throttle-store-{$i}@example.org",
                'role' => OrganizationInvitation::ROLE_VIEWER,
            ])->assertRedirect(route('organization.dashboard', [], false));
        }

        $this->actingAs($owner)->post(route('organization.invitations.store'), [
            'email' => 'throttle-store-10@example.org',
            'role' => OrganizationInvitation::ROLE_VIEWER,
        ])->assertStatus(429);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_org_owner_can_view_organization_dashboard(): void
    {
        $this->seed(RoleSeeder::class);
        $organization = Organization::factory()->create(['name_en' => 'Linked Org']);
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $organization->id])->save();
        $user->assignRole('org-owner');

        $this->actingAs($user)
            ->get(route('organization.dashboard'))
            ->assertOk()
            ->assertSee(__('Organization dashboard'), false)
            ->assertSee('data-testid="organization-dashboard-copy-page-url"', false)
            ->assertSee('<title>'.e(__('Organization dashboard').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('Linked Org', false);
    }

    public function test_org_owner_sees_pending_message_when_organization_pending(): void
    {
        $this->seed(RoleSeeder::class);
        $organization = Organization::factory()->pendingVerification()->create();
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $organization->id])->save();
        $user->assignRole('org-owner');

        $this->actingAs($user)
            ->get(route('organization.dashboard'))
            ->assertOk()
            ->assertSee(__('Organization pending verification title'), false);
    }

    public function test_volunteer_cannot_access_organization_dashboard(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)
            ->get(route('organization.dashboard'))
            ->assertForbidden();
    }

    public function test_org_owner_dashboard_shows_upcoming_events_count(): void
    {
        $this->seed(RoleSeeder::class);
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $organization->id])->save();
        $user->assignRole('org-owner');

        Event::factory()->create([
            'organization_id' => $organization->id,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'organization_id' => $organization->id,
            'event_starts_at' => now()->subDays(3),
            'event_ends_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->get(route('organization.dashboard'))
            ->assertOk()
            ->assertSee('data-testid="org-dashboard-upcoming-events-count">1<', false);
    }

    public function test_org_dashboard_upcoming_only_link_preserves_locale_query(): void
    {
        $this->seed(RoleSeeder::class);
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $organization->id])->save();
        $user->assignRole('org-owner');

        $this->actingAs($user)
            ->get(route('organization.dashboard', ['lang' => 'ar']))
            ->assertOk()
            ->assertSee('lang=ar', false)
            ->assertSee('timing=upcoming', false)
            ->assertSee('sort=starts_asc', false);
    }

    public function test_org_owner_can_filter_open_invitations_by_email(): void
    {
        $this->seed(RoleSeeder::class);
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $organization->id])->save();
        $user->assignRole('org-owner');

        OrganizationInvitation::factory()
            ->forOrganization($organization)
            ->invitedBy($user)
            ->create(['email' => 'filter_alpha_unique@example.com']);
        OrganizationInvitation::factory()
            ->forOrganization($organization)
            ->invitedBy($user)
            ->create(['email' => 'filter_beta_other@example.com']);

        $this->assertDatabaseHas('organization_invitations', [
            'email' => 'filter_alpha_unique@example.com',
            'organization_id' => $organization->id,
        ]);
        $user->refresh();
        $this->assertSame($organization->id, $user->organization_id);

        $this->actingAs($user)
            ->get(route('organization.dashboard', ['invitation_search' => 'filter_alpha_unique']))
            ->assertOk()
            ->assertSee('filter_alpha_unique@example.com', true)
            ->assertDontSee('filter_beta_other@example.com', true)
            ->assertSee('data-testid="org-dashboard-invitations-copy-filtered-url"', false);
    }

    public function test_org_owner_can_resubmit_rejected_organization_for_review(): void
    {
        $this->seed(RoleSeeder::class);
        $organization = Organization::factory()->create([
            'name_en' => 'Old Rejected Name',
            'verification_status' => Organization::VERIFICATION_REJECTED,
            'verification_review_note' => 'Please clarify.',
            'verification_reviewed_at' => now(),
        ]);
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $organization->id])->save();
        $user->assignRole('org-owner');

        $this->actingAs($user)
            ->post(route('organization.verification-resubmit'), [
                'name_en' => 'Updated Org Name',
                'name_ar' => 'اسم',
            ])
            ->assertRedirect(route('organization.dashboard', ['lang' => 'en'], false))
            ->assertSessionHas('status');

        $organization->refresh();
        $this->assertTrue($organization->isPendingVerification());
        $this->assertSame('Updated Org Name', $organization->name_en);
        $this->assertSame('اسم', $organization->name_ar);
        $this->assertNull($organization->verification_review_note);
    }

    public function test_org_manager_cannot_post_verification_resubmit(): void
    {
        $this->seed(RoleSeeder::class);
        $organization = Organization::factory()->create([
            'verification_status' => Organization::VERIFICATION_REJECTED,
        ]);
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $organization->id])->save();
        $user->assignRole('org-manager');

        $this->actingAs($user)
            ->post(route('organization.verification-resubmit'), [
                'name_en' => 'Hacked Name',
                'name_ar' => null,
            ])
            ->assertForbidden();
    }
}

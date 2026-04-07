<?php

namespace Tests\Feature;

use App\Mail\EventApplicationReviewedMail;
use App\Models\Event;
use App\Models\EventApplication;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrganizationEventApplicationPortalTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoles(): void
    {
        $this->seed(RoleSeeder::class);
    }

    /** @return array{0: Organization, 1: User} */
    private function approvedOrgManager(): array
    {
        $this->seedRoles();
        $org = Organization::factory()->create();
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $org->id])->save();
        $user->assignRole('org-manager');

        return [$org, $user];
    }

    public function test_pending_organization_cannot_access_applications_portal(): void
    {
        $this->seedRoles();
        $org = Organization::factory()->pendingVerification()->create();
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $org->id])->save();
        $user->assignRole('org-manager');

        $this->actingAs($user)->get(route('organization.event-applications.index'))->assertForbidden();
    }

    public function test_volunteer_cannot_access_applications_portal(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)->get(route('organization.event-applications.index'))->assertForbidden();
    }

    public function test_org_manager_sees_only_applications_for_own_events(): void
    {
        [$orgA, $manager] = $this->approvedOrgManager();
        $orgB = Organization::factory()->create();

        $eventA = Event::factory()->create([
            'organization_id' => $orgA->id,
            'application_required' => true,
            'title_en' => 'Event A Unique Title',
        ]);
        $eventB = Event::factory()->create([
            'organization_id' => $orgB->id,
            'application_required' => true,
            'title_en' => 'Event B Other Org',
        ]);

        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');

        EventApplication::factory()->create([
            'event_id' => $eventA->id,
            'user_id' => $volunteer->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);
        EventApplication::factory()->create([
            'event_id' => $eventB->id,
            'user_id' => $volunteer->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $response = $this->actingAs($manager)->get(route('organization.event-applications.index'));

        $response->assertOk()
            ->assertSee('<title>'.e(__('Applications for your organization').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('Event A Unique Title', false)
            ->assertDontSee('Event B Other Org', false)
            ->assertSee('data-testid="organization-event-applications-copy-filtered-url"', false);
    }

    public function test_org_manager_can_approve_pending_application(): void
    {
        Mail::fake();

        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create(['organization_id' => $org->id, 'application_required' => true]);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $app = EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $this->actingAs($manager)
            ->post(route('organization.event-applications.approve', $app))
            ->assertRedirect();

        $this->assertDatabaseHas('event_applications', [
            'id' => $app->id,
            'status' => EventApplication::STATUS_APPROVED,
        ]);

        Mail::assertQueued(EventApplicationReviewedMail::class);
    }

    public function test_org_manager_cannot_approve_application_for_another_organizations_event(): void
    {
        Mail::fake();

        [$orgA, $manager] = $this->approvedOrgManager();
        $orgB = Organization::factory()->create();
        $eventB = Event::factory()->create(['organization_id' => $orgB->id, 'application_required' => true]);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $app = EventApplication::factory()->create([
            'event_id' => $eventB->id,
            'user_id' => $volunteer->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $this->actingAs($manager)
            ->post(route('organization.event-applications.approve', $app))
            ->assertForbidden();

        $this->assertDatabaseHas('event_applications', [
            'id' => $app->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        Mail::assertNothingQueued();
    }

    public function test_org_viewer_can_view_index_without_approve_actions_in_ui(): void
    {
        $this->seedRoles();
        $org = Organization::factory()->create();
        $viewer = User::factory()->create();
        $viewer->forceFill(['organization_id' => $org->id])->save();
        $viewer->assignRole('org-viewer');

        $event = Event::factory()->create(['organization_id' => $org->id, 'application_required' => true]);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $this->actingAs($viewer)
            ->get(route('organization.event-applications.index'))
            ->assertOk()
            ->assertSee($event->title_en, false);

        $this->actingAs($viewer)
            ->post(route('organization.event-applications.approve', EventApplication::query()->firstOrFail()))
            ->assertForbidden();
    }

    public function test_org_applications_index_search_filters_by_volunteer_email(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create(['organization_id' => $org->id, 'application_required' => true]);

        $vMatch = User::factory()->create(['email' => 'uniquefilter@example.org', 'name' => 'Alpha Person']);
        $vMatch->assignRole('volunteer');
        $vOther = User::factory()->create(['email' => 'other@example.org', 'name' => 'Beta Person']);
        $vOther->assignRole('volunteer');

        EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $vMatch->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);
        EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $vOther->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $this->actingAs($manager)
            ->get(route('organization.event-applications.index', ['search' => 'uniquefilter@']))
            ->assertOk()
            ->assertSee('uniquefilter@example.org', false)
            ->assertDontSee('other@example.org', false);
    }

    public function test_org_applications_index_search_matches_email_with_underscore(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create(['organization_id' => $org->id, 'application_required' => true]);

        $vMatch = User::factory()->create(['email' => 'org_vol_x@example.org', 'name' => 'Underscore Org A']);
        $vMatch->assignRole('volunteer');
        $vOther = User::factory()->create(['email' => 'org_other_y@example.org', 'name' => 'Underscore Org B']);
        $vOther->assignRole('volunteer');

        EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $vMatch->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);
        EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $vOther->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $this->actingAs($manager)
            ->get(route('organization.event-applications.index', ['search' => 'org_vol']))
            ->assertOk()
            ->assertSee('org_vol_x@example.org', false)
            ->assertDontSee('org_other_y@example.org', false);
    }

    public function test_org_applications_index_rejects_event_id_from_another_organization(): void
    {
        [$orgA, $manager] = $this->approvedOrgManager();
        $orgB = Organization::factory()->create();
        $eventB = Event::factory()->create(['organization_id' => $orgB->id, 'application_required' => true]);

        $this->actingAs($manager)
            ->get(route('organization.event-applications.index', ['event_id' => $eventB->id]))
            ->assertSessionHasErrors('event_id');
    }

    public function test_org_applications_index_search_max_length_validation(): void
    {
        [$org, $manager] = $this->approvedOrgManager();

        $this->actingAs($manager)
            ->get(route('organization.event-applications.index', ['search' => str_repeat('a', 101)]))
            ->assertSessionHasErrors('search');
    }

    public function test_org_applications_index_sort_oldest_submitted_first_orders_by_created_at(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create(['organization_id' => $org->id, 'application_required' => true]);

        $vOld = User::factory()->create(['name' => 'OlderApplicantSortZ']);
        $vOld->assignRole('volunteer');
        $vNew = User::factory()->create(['name' => 'NewerApplicantSortY']);
        $vNew->assignRole('volunteer');

        $appOld = EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $vOld->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);
        $appNew = EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $vNew->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);
        DB::table('event_applications')->where('id', $appOld->id)->update(['created_at' => now()->subDays(3)]);
        DB::table('event_applications')->where('id', $appNew->id)->update(['created_at' => now()->subDay()]);

        $this->actingAs($manager)
            ->get(route('organization.event-applications.index', ['sort' => 'submitted_asc']))
            ->assertOk()
            ->assertSeeInOrder(['OlderApplicantSortZ', 'NewerApplicantSortY'], false);
    }

    public function test_org_applications_index_invalid_sort_returns_validation_error(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        Event::factory()->create(['organization_id' => $org->id, 'application_required' => true]);

        $this->actingAs($manager)
            ->get(route('organization.event-applications.index', ['sort' => 'invalid']))
            ->assertSessionHasErrors('sort');
    }

    public function test_org_manager_can_download_applications_csv_export(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'application_required' => true,
            'title_en' => 'Export Event CSV',
        ]);
        $volunteer = User::factory()->create(['name' => 'CsvApp Volunteer', 'email' => 'csvappvol@example.com']);
        $volunteer->assignRole('volunteer');
        EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'status' => EventApplication::STATUS_PENDING,
            'message' => 'Please let me help',
        ]);

        $response = $this->actingAs($manager)
            ->get(route('organization.event-applications.export'));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('csvappvol@example.com', $response->streamedContent());
        $this->assertStringContainsString('Export Event CSV', $response->streamedContent());
        $this->assertStringContainsString(EventApplication::STATUS_PENDING, $response->streamedContent());
    }

    public function test_org_viewer_can_download_applications_csv_export(): void
    {
        $this->seedRoles();
        $org = Organization::factory()->create();
        $viewer = User::factory()->create();
        $viewer->forceFill(['organization_id' => $org->id])->save();
        $viewer->assignRole('org-viewer');

        $event = Event::factory()->create(['organization_id' => $org->id, 'application_required' => true]);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'status' => EventApplication::STATUS_APPROVED,
        ]);

        $this->actingAs($viewer)
            ->get(route('organization.event-applications.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}

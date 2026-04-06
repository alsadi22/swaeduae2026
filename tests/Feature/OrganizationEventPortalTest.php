<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use App\Support\PublicLocale;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationEventPortalTest extends TestCase
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

    public function test_pending_organization_cannot_access_events_portal(): void
    {
        $this->seedRoles();
        $org = Organization::factory()->pendingVerification()->create();
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $org->id])->save();
        $user->assignRole('org-manager');

        $this->actingAs($user)->get(route('organization.events.index'))->assertForbidden();
    }

    public function test_volunteer_cannot_access_events_portal(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)->get(route('organization.events.index'))->assertForbidden();
    }

    public function test_org_viewer_can_list_events_but_cannot_open_create_form(): void
    {
        $this->seedRoles();
        $org = Organization::factory()->create();
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $org->id])->save();
        $user->assignRole('org-viewer');

        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Viewer Sees This Event',
        ]);

        $this->actingAs($user)
            ->get(route('organization.events.index'))
            ->assertOk()
            ->assertSee('Viewer Sees This Event', false);

        $this->actingAs($user)
            ->get(route('organization.events.create'))
            ->assertForbidden();
    }

    public function test_organization_portal_urls_include_preferred_locale_when_set(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $manager->forceFill(['locale_preferred' => 'ar'])->save();

        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Locale Pref Org Event',
        ]);

        $createUrl = route('organization.events.create', ['lang' => 'ar'], false);

        $this->actingAs($manager)
            ->get(route('organization.events.index'))
            ->assertOk()
            ->assertSee('Locale Pref Org Event', false)
            ->assertSee($createUrl, false);
    }

    public function test_org_manager_sees_only_own_organization_events(): void
    {
        [$orgA, $manager] = $this->approvedOrgManager();
        $orgB = Organization::factory()->create();

        Event::factory()->create([
            'organization_id' => $orgA->id,
            'title_en' => 'Org A Event Title',
        ]);
        Event::factory()->create([
            'organization_id' => $orgB->id,
            'title_en' => 'Org B Other Event',
        ]);

        $this->actingAs($manager)
            ->get(route('organization.events.index'))
            ->assertOk()
            ->assertSee('Org A Event Title', false)
            ->assertDontSee('Org B Other Event', false);
    }

    public function test_org_events_index_search_filters_by_title(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'UniqueSearchTitleXYZ',
            'title_ar' => null,
        ]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Other Event Title',
        ]);

        $this->actingAs($manager)
            ->get(route('organization.events.index', ['search' => 'UniqueSearchTitleXYZ']))
            ->assertOk()
            ->assertSee('UniqueSearchTitleXYZ', false)
            ->assertDontSee('Other Event Title', false);
    }

    public function test_org_events_index_search_matches_title_with_underscore_partial(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Org_Portal_Walk_2026_UniqueZ',
            'title_ar' => null,
        ]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Different Org Event',
        ]);

        $this->actingAs($manager)
            ->get(route('organization.events.index', ['search' => 'Portal_Walk']))
            ->assertOk()
            ->assertSee('Org_Portal_Walk_2026_UniqueZ', false)
            ->assertDontSee('Different Org Event', false);
    }

    public function test_org_events_index_upcoming_timing_excludes_past_events(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Future Org Event',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Past Org Event',
            'event_starts_at' => now()->subDays(3),
            'event_ends_at' => now()->subDay(),
        ]);

        $this->actingAs($manager)
            ->get(route('organization.events.index', ['timing' => 'upcoming']))
            ->assertOk()
            ->assertSee('Future Org Event', false)
            ->assertDontSee('Past Org Event', false);
    }

    public function test_org_events_index_past_timing_excludes_upcoming_events(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Future Only',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Past Only',
            'event_starts_at' => now()->subDays(3),
            'event_ends_at' => now()->subDay(),
        ]);

        $this->actingAs($manager)
            ->get(route('organization.events.index', ['timing' => 'past']))
            ->assertOk()
            ->assertSee('Past Only', false)
            ->assertDontSee('Future Only', false);
    }

    public function test_org_events_index_invalid_timing_returns_validation_error(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        Event::factory()->create(['organization_id' => $org->id]);

        $this->actingAs($manager)
            ->get(route('organization.events.index', ['timing' => 'invalid']))
            ->assertSessionHasErrors('timing');
    }

    public function test_org_events_index_sort_earliest_first_orders_by_start_time(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Later Start Event',
            'event_starts_at' => now()->addDays(5),
            'event_ends_at' => now()->addDays(5)->addHour(),
        ]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Sooner Start Event',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDay()->addHour(),
        ]);

        $this->actingAs($manager)
            ->get(route('organization.events.index', ['sort' => 'starts_asc']))
            ->assertOk()
            ->assertSeeInOrder(['Sooner Start Event', 'Later Start Event'], false);
    }

    public function test_org_events_index_invalid_sort_returns_validation_error(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        Event::factory()->create(['organization_id' => $org->id]);

        $this->actingAs($manager)
            ->get(route('organization.events.index', ['sort' => 'bogus']))
            ->assertSessionHasErrors('sort');
    }

    public function test_org_manager_can_create_event_for_own_organization(): void
    {
        [$org, $manager] = $this->approvedOrgManager();

        $payload = [
            'title_en' => 'Portal Created Event',
            'title_ar' => null,
            'capacity' => '',
            'application_required' => '0',
            'latitude' => '25.2',
            'longitude' => '55.27',
            'geofence_radius_meters' => '200',
            'geofence_strict' => '0',
            'min_gps_accuracy_meters' => '',
            'checkin_window_starts_at' => now()->subHour()->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
            'checkin_window_ends_at' => now()->addHours(3)->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
            'event_starts_at' => now()->addHour()->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
            'event_ends_at' => now()->addHours(2)->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
            'checkout_grace_minutes_after_event' => '30',
        ];

        $this->actingAs($manager)
            ->post(route('organization.events.store'), $payload)
            ->assertRedirect(route('organization.events.index', PublicLocale::query()));

        $this->assertDatabaseHas('events', [
            'organization_id' => $org->id,
            'title_en' => 'Portal Created Event',
        ]);
    }

    public function test_org_manager_cannot_update_another_organizations_event(): void
    {
        [$orgA, $manager] = $this->approvedOrgManager();
        $orgB = Organization::factory()->create();
        $eventB = Event::factory()->create(['organization_id' => $orgB->id]);

        $payload = [
            'title_en' => 'Hijacked',
            'title_ar' => null,
            'capacity' => '',
            'application_required' => '0',
            'latitude' => '25.2',
            'longitude' => '55.27',
            'geofence_radius_meters' => '200',
            'geofence_strict' => '0',
            'min_gps_accuracy_meters' => '',
            'checkin_window_starts_at' => $eventB->checkin_window_starts_at->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
            'checkin_window_ends_at' => $eventB->checkin_window_ends_at->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
            'event_starts_at' => $eventB->event_starts_at->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
            'event_ends_at' => $eventB->event_ends_at->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
            'checkout_grace_minutes_after_event' => '30',
        ];

        $this->actingAs($manager)
            ->put(route('organization.events.update', $eventB), $payload)
            ->assertForbidden();
    }

    public function test_org_coordinator_cannot_delete_event(): void
    {
        $this->seedRoles();
        $org = Organization::factory()->create();
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $org->id])->save();
        $user->assignRole('org-coordinator');

        $event = Event::factory()->create(['organization_id' => $org->id]);

        $this->actingAs($user)
            ->delete(route('organization.events.destroy', $event))
            ->assertForbidden();
    }

    public function test_org_manager_can_delete_own_organization_event(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create(['organization_id' => $org->id]);

        $this->actingAs($manager)
            ->delete(route('organization.events.destroy', $event))
            ->assertRedirect(route('organization.events.index', PublicLocale::query()));

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_org_manager_cannot_delete_event_with_volunteers_on_roster(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create(['organization_id' => $org->id]);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $this->actingAs($manager)
            ->delete(route('organization.events.destroy', $event))
            ->assertForbidden();

        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_org_coordinator_cannot_generate_checkpoint_signed_url(): void
    {
        $this->seedRoles();
        $org = Organization::factory()->create();
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $org->id])->save();
        $user->assignRole('org-coordinator');

        $event = Event::factory()->create(['organization_id' => $org->id]);

        $this->actingAs($user)
            ->post(route('organization.events.checkpoint-signed-url', $event), ['days' => 3])
            ->assertForbidden();
    }

    public function test_org_coordinator_cannot_open_event_create_or_edit(): void
    {
        $this->seedRoles();
        $org = Organization::factory()->create();
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $org->id])->save();
        $user->assignRole('org-coordinator');

        $event = Event::factory()->create(['organization_id' => $org->id]);

        $this->actingAs($user)->get(route('organization.events.create'))->assertForbidden();
        $this->actingAs($user)->get(route('organization.events.edit', $event))->assertForbidden();
    }

    public function test_org_manager_can_generate_checkpoint_signed_url(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create(['organization_id' => $org->id]);

        $this->actingAs($manager)
            ->post(route('organization.events.checkpoint-signed-url', $event), ['days' => 3])
            ->assertRedirect(route('organization.events.edit', array_merge(['event' => $event], PublicLocale::query())))
            ->assertSessionHas('checkpoint_signed_url');
    }

    public function test_roster_search_filters_volunteers(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create(['organization_id' => $org->id]);

        $alpha = User::factory()->create(['name' => 'Alpha UniqueName', 'email' => 'alpha@example.com']);
        $beta = User::factory()->create(['name' => 'Beta Other', 'email' => 'beta@example.com']);
        $alpha->assignRole('volunteer');
        $beta->assignRole('volunteer');
        $event->volunteers()->attach([$alpha->id, $beta->id]);

        $this->actingAs($manager)
            ->get(route('organization.events.roster', ['event' => $event, 'search' => 'UniqueName']))
            ->assertOk()
            ->assertSee('Alpha UniqueName', false)
            ->assertDontSee('Beta Other', false);
    }

    public function test_org_roster_csv_export_link_preserves_lang_query_on_roster_page(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create(['organization_id' => $org->id]);

        $exportUrl = route('organization.events.roster.export', ['event' => $event, 'lang' => 'ar'], false);

        $this->actingAs($manager)
            ->get(route('organization.events.roster', ['event' => $event, 'lang' => 'ar']))
            ->assertOk()
            ->assertSee($exportUrl, false);
    }

    public function test_org_manager_roster_csv_export_contains_volunteer_row(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Export Event',
        ]);
        $volunteer = User::factory()->create(['name' => 'CSV Volunteer', 'email' => 'csvvol@example.com']);
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $response = $this->actingAs($manager)
            ->get(route('organization.events.roster.export', $event));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('CSV Volunteer', $response->streamedContent());
        $this->assertStringContainsString('csvvol@example.com', $response->streamedContent());
    }

    public function test_org_manager_roster_csv_export_respects_search_filter(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create(['organization_id' => $org->id]);

        $match = User::factory()->create(['name' => 'Export Match Only', 'email' => 'match@example.com']);
        $other = User::factory()->create(['name' => 'Export Other Person', 'email' => 'other@example.com']);
        $match->assignRole('volunteer');
        $other->assignRole('volunteer');
        $event->volunteers()->attach([$match->id, $other->id]);

        $response = $this->actingAs($manager)
            ->get(route('organization.events.roster.export', ['event' => $event, 'search' => 'Match Only']));

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('Export Match Only', $content);
        $this->assertStringNotContainsString('Export Other Person', $content);
        $this->assertStringContainsString('-filtered.csv', $response->headers->get('content-disposition', ''));
    }

    public function test_org_viewer_can_download_roster_csv_export(): void
    {
        $this->seedRoles();
        $org = Organization::factory()->create();
        $viewer = User::factory()->create();
        $viewer->forceFill(['organization_id' => $org->id])->save();
        $viewer->assignRole('org-viewer');

        $event = Event::factory()->create(['organization_id' => $org->id]);
        $volunteer = User::factory()->create(['name' => 'Viewer Export Vol']);
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $response = $this->actingAs($viewer)
            ->get(route('organization.events.roster.export', $event));

        $response->assertOk();
        $this->assertStringContainsString('Viewer Export Vol', $response->streamedContent());
    }

    public function test_org_viewer_can_view_roster_for_own_organization_event(): void
    {
        $this->seedRoles();
        $org = Organization::factory()->create();
        $viewer = User::factory()->create(['name' => 'Portal Viewer User']);
        $viewer->forceFill(['organization_id' => $org->id])->save();
        $viewer->assignRole('org-viewer');

        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Roster Test Event',
        ]);

        $volunteer = User::factory()->create(['name' => 'Rostered Volunteer', 'email' => 'rostered@example.com']);
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $this->actingAs($viewer)
            ->get(route('organization.events.roster', $event))
            ->assertOk()
            ->assertSee('Roster Test Event', false)
            ->assertSee('Rostered Volunteer', false)
            ->assertSee('rostered@example.com', false)
            ->assertDontSee('Remove from roster', false);
    }

    public function test_org_manager_cannot_view_roster_for_another_organizations_event(): void
    {
        [$orgA, $manager] = $this->approvedOrgManager();
        $orgB = Organization::factory()->create();
        $eventB = Event::factory()->create(['organization_id' => $orgB->id]);

        $this->actingAs($manager)
            ->get(route('organization.events.roster', $eventB))
            ->assertForbidden();
    }

    public function test_org_manager_roster_shows_attendance_status(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create(['organization_id' => $org->id]);
        $volunteer = User::factory()->create(['name' => 'Status Volunteer']);
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHours(2),
            'checked_out_at' => now()->subHour(),
            'minutes_worked' => 60,
        ]);

        $this->actingAs($manager)
            ->get(route('organization.events.roster', $event))
            ->assertOk()
            ->assertSee('Status Volunteer', false)
            ->assertSee('Checked out', false)
            ->assertSee('Verified minutes', false);
    }

    public function test_org_viewer_cannot_remove_roster_volunteer(): void
    {
        $this->seedRoles();
        $org = Organization::factory()->create();
        $viewer = User::factory()->create();
        $viewer->forceFill(['organization_id' => $org->id])->save();
        $viewer->assignRole('org-viewer');

        $event = Event::factory()->create(['organization_id' => $org->id]);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $this->actingAs($viewer)
            ->delete(route('organization.events.roster.volunteers.destroy', [$event, $volunteer]))
            ->assertForbidden();
    }

    public function test_org_coordinator_can_remove_volunteer_without_attendance(): void
    {
        $this->seedRoles();
        $org = Organization::factory()->create();
        $coordinator = User::factory()->create();
        $coordinator->forceFill(['organization_id' => $org->id])->save();
        $coordinator->assignRole('org-coordinator');

        $event = Event::factory()->create(['organization_id' => $org->id]);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $this->actingAs($coordinator)
            ->delete(route('organization.events.roster.volunteers.destroy', [$event, $volunteer]))
            ->assertRedirect(route('organization.events.roster', array_merge(['event' => $event], PublicLocale::query())));

        $this->assertFalse($event->fresh()->userIsOnRoster($volunteer));
    }

    public function test_org_manager_cannot_remove_volunteer_with_attendance(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create(['organization_id' => $org->id]);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_PENDING,
        ]);

        $this->actingAs($manager)
            ->delete(route('organization.events.roster.volunteers.destroy', [$event, $volunteer]))
            ->assertForbidden();

        $this->assertTrue($event->fresh()->userIsOnRoster($volunteer));
    }

    public function test_org_manager_cannot_remove_user_not_on_roster(): void
    {
        [$org, $manager] = $this->approvedOrgManager();
        $event = Event::factory()->create(['organization_id' => $org->id]);
        $stranger = User::factory()->create();
        $stranger->assignRole('volunteer');

        $this->actingAs($manager)
            ->delete(route('organization.events.roster.volunteers.destroy', [$event, $stranger]))
            ->assertForbidden();
    }

    public function test_org_manager_cannot_remove_roster_volunteer_for_other_organization_event(): void
    {
        [$orgA, $manager] = $this->approvedOrgManager();
        $orgB = Organization::factory()->create();
        $eventB = Event::factory()->create(['organization_id' => $orgB->id]);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $eventB->volunteers()->attach($volunteer->id);

        $this->actingAs($manager)
            ->delete(route('organization.events.roster.volunteers.destroy', [$eventB, $volunteer]))
            ->assertForbidden();
    }
}

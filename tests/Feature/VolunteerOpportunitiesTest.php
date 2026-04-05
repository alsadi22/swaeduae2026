<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventApplication;
use App\Models\Organization;
use App\Models\User;
use App\Models\VolunteerProfile;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolunteerOpportunitiesTest extends TestCase
{
    use RefreshDatabase;

    private function volunteerUser(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');
        VolunteerProfile::factory()->forUser($user)->create();

        return $user;
    }

    private function volunteerUserWithoutCommitmentProfile(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        return $user;
    }

    public function test_opportunities_index_ok_when_empty(): void
    {
        $this->get(route('volunteer.opportunities.index'))
            ->assertOk()
            ->assertSee(__('No open opportunities right now'), false);
    }

    public function test_opportunities_lists_future_events(): void
    {
        $org = Organization::query()->create([
            'name_en' => 'Test Org',
            'name_ar' => null,
        ]);

        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Unique Park Cleanup',
            'title_ar' => null,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $this->get(route('volunteer.opportunities.index'))
            ->assertOk()
            ->assertSee('Unique Park Cleanup', false);

        $this->get(route('volunteer.opportunities.show', $event))
            ->assertOk()
            ->assertSee('Unique Park Cleanup', false)
            ->assertSee('Test Org', false);
    }

    public function test_past_events_not_listed(): void
    {
        $org = Organization::query()->create([
            'name_en' => 'Past Org',
            'name_ar' => null,
        ]);

        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Old Event Hidden',
            'event_starts_at' => now()->subDays(5),
            'event_ends_at' => now()->subDay(),
            'checkin_window_starts_at' => now()->subDays(5),
            'checkin_window_ends_at' => now()->subDay(),
        ]);

        $this->get(route('volunteer.opportunities.index'))
            ->assertOk()
            ->assertDontSee('Old Event Hidden', false);
    }

    public function test_guest_cannot_post_join(): void
    {
        $org = Organization::query()->create(['name_en' => 'Org', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $this->post(route('volunteer.opportunities.join', $event))
            ->assertRedirect(route('login'));
    }

    public function test_volunteer_without_minimum_profile_cannot_join_open_roster(): void
    {
        $user = $this->volunteerUserWithoutCommitmentProfile();
        $org = Organization::query()->create(['name_en' => 'Host', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Needs Profile',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $this->actingAs($user)->post(route('volunteer.opportunities.join', $event))->assertForbidden();
        $this->assertFalse($event->fresh()->userIsOnRoster($user));
    }

    public function test_volunteer_can_join_roster_on_open_event(): void
    {
        $user = $this->volunteerUser();
        $org = Organization::query()->create(['name_en' => 'Host', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Joinable Event',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($user)->post(route('volunteer.opportunities.join', $event));

        $response->assertRedirect(route('volunteer.opportunities.show', $event));
        $this->assertTrue($event->fresh()->userIsOnRoster($user));
    }

    public function test_non_volunteer_cannot_join_roster(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $org = Organization::query()->create(['name_en' => 'Host', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $this->actingAs($user)->post(route('volunteer.opportunities.join', $event))->assertForbidden();
    }

    public function test_volunteer_cannot_join_after_event_ended(): void
    {
        $user = $this->volunteerUser();
        $org = Organization::query()->create(['name_en' => 'Host', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'event_starts_at' => now()->subDays(3),
            'event_ends_at' => now()->subHour(),
            'checkin_window_starts_at' => now()->subDays(3),
            'checkin_window_ends_at' => now()->subHour(),
        ]);

        $this->actingAs($user)->post(route('volunteer.opportunities.join', $event))->assertForbidden();
    }

    public function test_volunteer_cannot_join_when_roster_is_full(): void
    {
        $this->seed(RoleSeeder::class);
        $first = User::factory()->create();
        $first->assignRole('volunteer');
        $second = User::factory()->create();
        $second->assignRole('volunteer');
        VolunteerProfile::factory()->forUser($second)->create();

        $org = Organization::query()->create(['name_en' => 'Host', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'capacity' => 1,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $event->volunteers()->attach($first->id);

        $this->actingAs($second)->post(route('volunteer.opportunities.join', $event))->assertForbidden();
        $this->assertFalse($event->fresh()->userIsOnRoster($second));
    }

    public function test_opportunities_list_shows_requires_application_badge(): void
    {
        $org = Organization::query()->create(['name_en' => 'Badge Org', 'name_ar' => null]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Gated Opportunity',
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $this->get(route('volunteer.opportunities.index'))
            ->assertOk()
            ->assertSee('Gated Opportunity', false)
            ->assertSee(__('Requires application'), false);
    }

    public function test_opportunity_show_shows_notice_when_volunteer_has_pending_application_elsewhere(): void
    {
        $user = $this->volunteerUser();
        $org = Organization::query()->create(['name_en' => 'Other Pending Org', 'name_ar' => null]);

        $otherEvent = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Other Pending Event',
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $currentEvent = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Current Opportunity Page',
            'application_required' => true,
            'event_starts_at' => now()->addDays(3),
            'event_ends_at' => now()->addDays(4),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(4),
        ]);

        EventApplication::query()->create([
            'event_id' => $otherEvent->id,
            'user_id' => $user->id,
            'status' => EventApplication::STATUS_PENDING,
            'message' => str_repeat('x', 30).' pending elsewhere',
        ]);

        $this->actingAs($user)
            ->get(route('volunteer.opportunities.show', $currentEvent))
            ->assertOk()
            ->assertSee('data-testid="pending-applications-other-events-notice"', false)
            ->assertSee('one pending application for another opportunity', false);

        $this->actingAs($user)
            ->get(route('volunteer.opportunities.show', $otherEvent))
            ->assertOk()
            ->assertDontSee('data-testid="pending-applications-other-events-notice"', false);
    }

    public function test_opportunity_show_shows_requires_application_badge(): void
    {
        $org = Organization::query()->create(['name_en' => 'Show Badge Org', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Detail Page Gated',
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $this->get(route('volunteer.opportunities.show', $event))
            ->assertOk()
            ->assertSee('Detail Page Gated', false)
            ->assertSee(__('Requires application'), false);
    }

    public function test_opportunities_search_filters_by_title(): void
    {
        $org = Organization::query()->create(['name_en' => 'Org', 'name_ar' => null]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Alpha Workshop Unique',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Beta Clinic Unique',
            'event_starts_at' => now()->addDays(3),
            'event_ends_at' => now()->addDays(4),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(4),
        ]);

        $this->get(route('volunteer.opportunities.index', ['q' => 'Beta Clinic']))
            ->assertOk()
            ->assertSee('Beta Clinic Unique', false)
            ->assertDontSee('Alpha Workshop Unique', false);
    }

    public function test_opportunities_search_matches_host_organization_name(): void
    {
        $orgMatch = Organization::query()->create([
            'name_en' => 'Desert Conservation League UniqueOrg',
            'name_ar' => null,
        ]);
        $orgOther = Organization::query()->create([
            'name_en' => 'Other Host Org',
            'name_ar' => null,
        ]);

        Event::factory()->create([
            'organization_id' => $orgMatch->id,
            'title_en' => 'Generic Cleanup Day',
            'title_ar' => null,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'organization_id' => $orgOther->id,
            'title_en' => 'Another Generic Day',
            'title_ar' => null,
            'event_starts_at' => now()->addDays(3),
            'event_ends_at' => now()->addDays(4),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(4),
        ]);

        $this->get(route('volunteer.opportunities.index', ['q' => 'Conservation League']))
            ->assertOk()
            ->assertSee('Generic Cleanup Day', false)
            ->assertDontSee('Another Generic Day', false);
    }

    public function test_opportunities_search_matches_title_with_underscore(): void
    {
        $org = Organization::query()->create(['name_en' => 'Underscore Search Org', 'name_ar' => null]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Winter_Cleanup_2026_UniqueMarker',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Summer Festival Day',
            'event_starts_at' => now()->addDays(3),
            'event_ends_at' => now()->addDays(4),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(4),
        ]);

        $this->get(route('volunteer.opportunities.index', ['q' => 'Winter_Cleanup']))
            ->assertOk()
            ->assertSee('Winter_Cleanup_2026_UniqueMarker', false)
            ->assertDontSee('Summer Festival Day', false);
    }

    public function test_opportunities_sort_starts_late_orders_newest_first(): void
    {
        $org = Organization::query()->create(['name_en' => 'Sort Org', 'name_ar' => null]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Sooner Sort Marker',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Later Sort Marker',
            'event_starts_at' => now()->addWeek(),
            'event_ends_at' => now()->addWeek()->addDay(),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addWeek()->addDay(),
        ]);

        $this->get(route('volunteer.opportunities.index', ['sort' => 'starts_late']))
            ->assertOk()
            ->assertSeeTextInOrder(['Later Sort Marker', 'Sooner Sort Marker']);
    }

    public function test_opportunities_index_is_paginated(): void
    {
        $org = Organization::query()->create(['name_en' => 'Paginate Org', 'name_ar' => null]);
        for ($i = 0; $i < 16; $i++) {
            Event::factory()->create([
                'organization_id' => $org->id,
                'title_en' => 'Paginated Opportunity '.$i,
                'event_starts_at' => now()->addDays($i + 1),
                'event_ends_at' => now()->addDays($i + 2),
                'checkin_window_starts_at' => now(),
                'checkin_window_ends_at' => now()->addDays($i + 2),
            ]);
        }

        $first = $this->get(route('volunteer.opportunities.index'))->assertOk();
        $first->assertSee('Paginated Opportunity 0', false);
        $first->assertSee('Paginated Opportunity 14', false);
        $first->assertDontSee('Paginated Opportunity 15', false);

        $this->get(route('volunteer.opportunities.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Paginated Opportunity 15', false);
    }

    public function test_opportunities_search_empty_shows_no_match_message(): void
    {
        $org = Organization::query()->create(['name_en' => 'Org', 'name_ar' => null]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Visible Title',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $this->get(route('volunteer.opportunities.index', ['q' => 'NoSuchTitleXYZ123']))
            ->assertOk()
            ->assertSee(__('No opportunities match your search.'), false)
            ->assertDontSee(__('No open opportunities right now'), false);
    }

    public function test_opportunities_entry_filter_open_vs_application(): void
    {
        $org = Organization::query()->create(['name_en' => 'Entry Org', 'name_ar' => null]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Open Roster Event',
            'application_required' => false,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Application Gate Event',
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $this->get(route('volunteer.opportunities.index', ['entry' => 'open']))
            ->assertOk()
            ->assertSee('Open Roster Event', false)
            ->assertDontSee('Application Gate Event', false);

        $this->get(route('volunteer.opportunities.index', ['entry' => 'application']))
            ->assertOk()
            ->assertSee('Application Gate Event', false)
            ->assertDontSee('Open Roster Event', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventApplication;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardVolunteerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_lists_upcoming_rostered_events(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $org = Organization::query()->create(['name_en' => 'Org X', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Dashboard Listed Event',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $event->volunteers()->attach($user->id);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Listed Event', false);
    }

    public function test_volunteer_dashboard_shows_stay_informed_section(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('Stay informed'), false)
            ->assertSee(__('Manage email preferences'), false);
    }

    public function test_volunteer_dashboard_links_to_programs_and_media(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-testid="dashboard-discover-programs"', false)
            ->assertSee('data-testid="dashboard-discover-media"', false);
    }

    public function test_admin_without_volunteer_role_sees_no_volunteer_section_titles(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee(__('Your upcoming volunteer commitments'), false);
        $response->assertDontSee(__('Your opportunity applications'), false);
    }

    public function test_dashboard_lists_opportunity_applications(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $org = Organization::factory()->create();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Applied Event Title',
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(__('Your opportunity applications'), false);
        $response->assertSee('Applied Event Title', false);
        $response->assertSee(__('Application status pending'), false);
    }

    public function test_dashboard_lists_past_rostered_events(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $org = Organization::query()->create(['name_en' => 'Past Org', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Finished Workshop',
            'event_starts_at' => now()->subDays(3),
            'event_ends_at' => now()->subDay(),
            'checkin_window_starts_at' => now()->subDays(3),
            'checkin_window_ends_at' => now()->subDay(),
        ]);
        $event->volunteers()->attach($user->id);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(__('Past volunteer commitments'), false);
        $response->assertSee('Finished Workshop', false);
    }

    public function test_dashboard_shows_verified_volunteer_minutes_from_attendance(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $event = Event::factory()->create([
            'event_starts_at' => now()->subDays(2),
            'event_ends_at' => now()->subDay(),
        ]);
        $event->volunteers()->attach($user->id);

        Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subDays(2),
            'checked_out_at' => now()->subDays(2)->addHours(2),
            'minutes_worked' => 333,
        ]);

        $otherEvent = Event::factory()->create([
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);
        $otherEvent->volunteers()->attach($user->id);
        Attendance::query()->create([
            'event_id' => $otherEvent->id,
            'user_id' => $user->id,
            'state' => Attendance::STATE_CHECKED_IN,
            'checked_in_at' => now(),
            'minutes_worked' => null,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(__('Verified volunteer time'), false);
        $response->assertSee('data-testid="dashboard-verified-minutes-total"', false);
        $response->assertSee('class="sr-only">333</span>', false);
        $response->assertSee('data-testid="dashboard-verified-sessions-count"', false);
        $response->assertSee('class="sr-only">1</span>', false);
        $response->assertSee('data-testid="dashboard-verified-hours-rounded"', false);
        $response->assertSee('class="sr-only">6</span>', false);
    }

    public function test_dashboard_filters_opportunity_applications_by_status(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $org = Organization::factory()->create();
        $eventPending = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Pending Only Event',
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $eventApproved = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Approved Only Event',
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        EventApplication::factory()->create([
            'event_id' => $eventPending->id,
            'user_id' => $user->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);
        EventApplication::factory()->create([
            'event_id' => $eventApproved->id,
            'user_id' => $user->id,
            'status' => EventApplication::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', [
            'application_status' => EventApplication::STATUS_APPROVED,
        ]));

        $response->assertOk();
        $response->assertSee('Approved Only Event', false);
        $response->assertDontSee('Pending Only Event', false);
    }

    public function test_past_roster_commitments_are_paginated_on_dashboard(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $org = Organization::query()->create(['name_en' => 'Past Org Paginate', 'name_ar' => null]);
        for ($i = 0; $i < 6; $i++) {
            $event = Event::factory()->create([
                'organization_id' => $org->id,
                'title_en' => 'Past Dash Paginate '.$i,
                'event_starts_at' => now()->subDays(20 - $i),
                'event_ends_at' => now()->subDays(10 - $i),
            ]);
            $event->volunteers()->attach($user->id);
        }

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Past Dash Paginate 5', false)
            ->assertDontSee('Past Dash Paginate 0', false);

        $this->actingAs($user)
            ->get(route('dashboard', ['past_page' => 2]))
            ->assertOk()
            ->assertSee('Past Dash Paginate 0', false)
            ->assertDontSee('Past Dash Paginate 5', false);
    }

    public function test_upcoming_roster_commitments_are_paginated_on_dashboard(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $org = Organization::query()->create(['name_en' => 'Upcoming Org Paginate', 'name_ar' => null]);
        for ($i = 0; $i < 6; $i++) {
            $event = Event::factory()->create([
                'organization_id' => $org->id,
                'title_en' => 'Upcoming Dash Paginate '.$i,
                'event_starts_at' => now()->addDays($i + 1),
                'event_ends_at' => now()->addDays($i + 2),
                'checkin_window_starts_at' => now(),
                'checkin_window_ends_at' => now()->addDays($i + 3),
            ]);
            $event->volunteers()->attach($user->id);
        }

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Upcoming Dash Paginate 0', false)
            ->assertSee('Upcoming Dash Paginate 4', false)
            ->assertDontSee('Upcoming Dash Paginate 5', false);

        $this->actingAs($user)
            ->get(route('dashboard', ['upcoming_page' => 2]))
            ->assertOk()
            ->assertSee('Upcoming Dash Paginate 5', false)
            ->assertDontSee('Upcoming Dash Paginate 0', false);
    }

    public function test_opportunity_applications_are_paginated_on_dashboard(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $org = Organization::factory()->create();
        for ($i = 0; $i < 11; $i++) {
            $event = Event::factory()->create([
                'organization_id' => $org->id,
                'title_en' => 'App Paginate Event '.$i,
                'application_required' => true,
                'event_starts_at' => now()->addDay(),
                'event_ends_at' => now()->addDays(2),
                'checkin_window_starts_at' => now(),
                'checkin_window_ends_at' => now()->addDays(2),
            ]);
            $app = EventApplication::factory()->create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'status' => EventApplication::STATUS_PENDING,
            ]);
            DB::table('event_applications')->where('id', $app->id)->update([
                'updated_at' => now()->addSeconds($i),
            ]);
        }

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('App Paginate Event 10', false)
            ->assertSee('App Paginate Event 1', false)
            ->assertDontSee('App Paginate Event 0', false);

        $this->actingAs($user)
            ->get(route('dashboard', ['app_page' => 2]))
            ->assertOk()
            ->assertSee('App Paginate Event 0', false)
            ->assertDontSee('App Paginate Event 10', false);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlaggedAttendanceAdminTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_guest_redirected_from_flagged_attendance_index(): void
    {
        $this->get(route('admin.flagged-attendance.index'))->assertRedirect();
    }

    public function test_volunteer_cannot_access_flagged_attendance_index(): void
    {
        $this->seed(RoleSeeder::class);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');

        $this->actingAs($volunteer)->get(route('admin.flagged-attendance.index'))->assertForbidden();
    }

    public function test_admin_sees_attendance_with_suspicion_flags(): void
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create();
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
            'minutes_worked' => 45,
            'suspicion_flags' => ['low_gps_accuracy'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.flagged-attendance.index'))
            ->assertOk()
            ->assertSee('low_gps_accuracy', false)
            ->assertSee($event->title_en, false);
    }

    public function test_admin_can_filter_flagged_attendance_by_event(): void
    {
        $admin = $this->adminUser();
        $eventA = Event::factory()->create();
        $eventB = Event::factory()->create();
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $eventA->volunteers()->attach($volunteer->id);
        $eventB->volunteers()->attach($volunteer->id);

        Attendance::query()->create([
            'event_id' => $eventA->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
            'suspicion_flags' => ['outside_geofence'],
        ]);
        Attendance::query()->create([
            'event_id' => $eventB->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
            'suspicion_flags' => ['rapid_repeated_attempts'],
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.flagged-attendance.index', ['event_id' => $eventA->id]));

        $response->assertOk()
            ->assertSee('outside_geofence', false)
            ->assertDontSee('rapid_repeated_attempts', false);
    }

    public function test_admin_can_filter_flagged_attendance_by_volunteer_name(): void
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create();
        $vMatch = User::factory()->create([
            'email' => 'flaggedunique@example.org',
            'name' => 'UniqueFlaggedVolunteerZ',
        ]);
        $vMatch->assignRole('volunteer');
        $vOther = User::factory()->create([
            'email' => 'otherflag@example.org',
            'name' => 'OtherFlagVolunteerQ',
        ]);
        $vOther->assignRole('volunteer');
        $event->volunteers()->attach([$vMatch->id, $vOther->id]);

        Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $vMatch->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
            'suspicion_flags' => ['low_gps_accuracy'],
        ]);
        Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $vOther->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
            'suspicion_flags' => ['outside_geofence'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.flagged-attendance.index', ['search' => 'UniqueFlaggedVolunteer']))
            ->assertOk()
            ->assertSee('UniqueFlaggedVolunteerZ', false)
            ->assertDontSee('OtherFlagVolunteerQ', false);
    }

    public function test_flagged_attendance_index_search_max_length_validation(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.flagged-attendance.index', ['search' => str_repeat('c', 101)]))
            ->assertSessionHasErrors('search');
    }

    public function test_admin_nav_shows_flagged_attendance_badge_count(): void
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create();
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
            'minutes_worked' => 30,
            'suspicion_flags' => ['low_gps_accuracy'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.flagged-attendance.index'))
            ->assertOk()
            ->assertSee('data-testid="flagged-attendance-nav-badge"', false);
    }
}

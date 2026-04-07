<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolunteerAttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_can_filter_attendance_by_state(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $eventOut = Event::factory()->create([
            'event_starts_at' => now()->subDays(2),
            'event_ends_at' => now()->subDay(),
        ]);
        $eventIn = Event::factory()->create([
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);
        $eventOut->volunteers()->attach($user->id);
        $eventIn->volunteers()->attach($user->id);

        Attendance::query()->create([
            'event_id' => $eventOut->id,
            'user_id' => $user->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subDays(2),
            'checked_out_at' => now()->subDays(2)->addHour(),
            'minutes_worked' => 60,
        ]);
        Attendance::query()->create([
            'event_id' => $eventIn->id,
            'user_id' => $user->id,
            'state' => Attendance::STATE_CHECKED_IN,
            'checked_in_at' => now(),
            'minutes_worked' => null,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.attendance.index', [
            'state' => Attendance::STATE_CHECKED_OUT,
        ]));

        $response->assertOk();
        $response->assertSee($eventOut->titleForLocale(), false);
        $response->assertDontSee($eventIn->titleForLocale(), false);
        $response->assertSee('data-testid="attendance-copy-filtered-url"', false);
        $response->assertSee('<title>'.e(__('My attendance').' — '.__('SwaedUAE')).'</title>', false);
        $response->assertSee('rel="manifest"', false);
    }

    public function test_volunteer_sees_minutes_adjustment_note_on_checked_out_attendance(): void
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
            'checked_out_at' => now()->subDays(2)->addHour(),
            'minutes_worked' => 60,
            'minutes_adjustment' => 15,
            'minutes_adjustment_note' => 'Coordinator confirmed late stay.',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.attendance.index'))
            ->assertOk()
            ->assertSee('Coordinator confirmed late stay.', false)
            ->assertSee(__('Adjustment note'), false);
    }

    public function test_invalid_attendance_state_filter_shows_validation_error(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)
            ->from(route('dashboard.attendance.index'))
            ->get(route('dashboard.attendance.index', ['state' => 'not-a-state']))
            ->assertRedirect(route('dashboard.attendance.index'))
            ->assertSessionHasErrors('state');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AttendanceCheckpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_volunteer_can_check_in_within_geofence(): void
    {
        $event = Event::factory()->create([
            'latitude' => 25.2048,
            'longitude' => 55.2708,
            'geofence_strict' => false,
            'min_gps_accuracy_meters' => 500,
        ]);

        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $signedUrl = URL::temporarySignedRoute(
            'attendance.checkpoint.show',
            now()->addHour(),
            ['event' => $event->uuid]
        );

        $this->actingAs($volunteer)
            ->get($signedUrl)
            ->assertOk()
            ->assertSee($event->title_en, false)
            ->assertSee(__('Event summary'), false)
            ->assertSee(__('Attendance checkpoint session hint'), false)
            ->assertSee(__('Back to opportunity'), false);

        $this->actingAs($volunteer)
            ->from($signedUrl)
            ->post(route('attendance.checkpoint.store', $event), [
                'action' => 'check_in',
                'latitude' => 25.2048,
                'longitude' => 55.2708,
                'accuracy_meters' => 25,
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => 'checked_in',
        ]);

        $this->assertDatabaseHas('checkin_attempts', [
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'attempt_type' => 'check_in',
        ]);
    }

    public function test_user_not_on_roster_cannot_check_in(): void
    {
        $event = Event::factory()->create();
        $stranger = User::factory()->create();
        $stranger->assignRole('volunteer');

        $signedUrl = URL::temporarySignedRoute(
            'attendance.checkpoint.show',
            now()->addHour(),
            ['event' => $event->uuid]
        );

        $this->actingAs($stranger)->get($signedUrl)->assertOk();

        $this->actingAs($stranger)
            ->from($signedUrl)
            ->post(route('attendance.checkpoint.store', $event), [
                'action' => 'check_in',
                'latitude' => (float) $event->latitude,
                'longitude' => (float) $event->longitude,
                'accuracy_meters' => 10,
            ])
            ->assertSessionHas('error');
    }

    public function test_strict_geofence_rejects_distant_coordinates(): void
    {
        $event = Event::factory()->strictGeofence()->create([
            'latitude' => 25.2048,
            'longitude' => 55.2708,
            'geofence_radius_meters' => 50,
            'min_gps_accuracy_meters' => 500,
        ]);

        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $signedUrl = URL::temporarySignedRoute(
            'attendance.checkpoint.show',
            now()->addHour(),
            ['event' => $event->uuid]
        );

        $this->actingAs($volunteer)->get($signedUrl)->assertOk();

        $this->actingAs($volunteer)
            ->from($signedUrl)
            ->post(route('attendance.checkpoint.store', $event), [
                'action' => 'check_in',
                'latitude' => 25.3000,
                'longitude' => 55.3000,
                'accuracy_meters' => 10,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('attendances', [
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => 'checked_in',
        ]);
    }

    public function test_checkout_sets_minutes_worked(): void
    {
        $this->travelTo(Carbon::parse('2026-04-03 10:00:00', 'UTC'));

        $event = Event::factory()->create([
            'latitude' => 25.2048,
            'longitude' => 55.2708,
            'geofence_strict' => false,
            'min_gps_accuracy_meters' => 500,
            'checkin_window_starts_at' => now()->subHour(),
            'checkin_window_ends_at' => now()->addHours(4),
            'event_starts_at' => now()->subMinutes(30),
            'event_ends_at' => now()->addHours(2),
            'checkout_grace_minutes_after_event' => 120,
        ]);

        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $signedUrl = URL::temporarySignedRoute(
            'attendance.checkpoint.show',
            now()->addHour(),
            ['event' => $event->uuid]
        );

        $this->actingAs($volunteer)->get($signedUrl)->assertOk();

        $this->actingAs($volunteer)
            ->from($signedUrl)
            ->post(route('attendance.checkpoint.store', $event), [
                'action' => 'check_in',
                'latitude' => 25.2048,
                'longitude' => 55.2708,
                'accuracy_meters' => 25,
            ])
            ->assertSessionHas('success');

        $this->travel(45)->minutes();

        $this->actingAs($volunteer)->get($signedUrl)->assertOk();

        $this->actingAs($volunteer)
            ->from($signedUrl)
            ->post(route('attendance.checkpoint.store', $event), [
                'action' => 'check_out',
                'latitude' => 25.2048,
                'longitude' => 55.2708,
                'accuracy_meters' => 25,
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => 'checked_out',
            'minutes_worked' => 45,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolunteerLeaveRosterTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_can_leave_before_event_starts(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');
        $org = Organization::query()->create(['name_en' => 'Org', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'event_starts_at' => now()->addDays(2),
            'event_ends_at' => now()->addDays(3),
            'checkin_window_starts_at' => now()->addDay(),
            'checkin_window_ends_at' => now()->addDays(3),
        ]);
        $event->volunteers()->attach($user->id);

        $response = $this->actingAs($user)->post(route('volunteer.opportunities.leave', $event));

        $response->assertRedirect(route('volunteer.opportunities.show', $event));
        $this->assertFalse($event->fresh()->userIsOnRoster($user));
    }

    public function test_volunteer_cannot_leave_after_event_started(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');
        $org = Organization::query()->create(['name_en' => 'Org', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'event_starts_at' => now()->subHour(),
            'event_ends_at' => now()->addHours(2),
            'checkin_window_starts_at' => now()->subHours(2),
            'checkin_window_ends_at' => now()->addHours(2),
        ]);
        $event->volunteers()->attach($user->id);

        $this->actingAs($user)->post(route('volunteer.opportunities.leave', $event))->assertForbidden();
        $this->assertTrue($event->fresh()->userIsOnRoster($user));
    }

    public function test_non_rostered_volunteer_cannot_leave(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');
        $org = Organization::query()->create(['name_en' => 'Org', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $this->actingAs($user)->post(route('volunteer.opportunities.leave', $event))->assertForbidden();
    }
}

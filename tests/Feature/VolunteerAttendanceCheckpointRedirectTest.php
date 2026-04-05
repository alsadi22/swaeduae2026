<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolunteerAttendanceCheckpointRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function volunteerWithRosteredEvent(): array
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
        $event->volunteers()->attach($user->id);

        return [$user, $event];
    }

    public function test_rostered_volunteer_gets_redirect_to_signed_checkpoint_url(): void
    {
        [$user, $event] = $this->volunteerWithRosteredEvent();

        $response = $this->actingAs($user)->get(route('volunteer.opportunities.attendance', $event));

        $response->assertStatus(302);
        $location = $response->headers->get('Location');
        $this->assertNotNull($location);
        $this->assertStringContainsString('/attendance/checkpoint/', $location);
        $this->assertStringContainsString('signature=', $location);
        $this->assertStringContainsString('expires=', $location);
    }

    public function test_non_rostered_volunteer_forbidden(): void
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

        $this->actingAs($user)->get(route('volunteer.opportunities.attendance', $event))->assertForbidden();
    }

    public function test_guest_redirected_to_login(): void
    {
        $org = Organization::query()->create(['name_en' => 'Org', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $this->get(route('volunteer.opportunities.attendance', $event))->assertRedirect(route('login', ['lang' => 'en'], absolute: false));
    }
}

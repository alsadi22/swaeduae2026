<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventApplication;
use App\Models\Organization;
use App\Models\User;
use App\Models\VolunteerProfile;
use App\Support\PublicLocale;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OwenIt\Auditing\Models\Audit;
use Tests\TestCase;

class VolunteerEventApplicationTest extends TestCase
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

    private function futureEventWithApplicationRequired(): Event
    {
        $org = Organization::factory()->create();

        return Event::factory()->create([
            'organization_id' => $org->id,
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
    }

    public function test_volunteer_without_minimum_profile_cannot_apply(): void
    {
        $user = $this->volunteerUserWithoutCommitmentProfile();
        $event = $this->futureEventWithApplicationRequired();

        $this->actingAs($user)
            ->post(route('volunteer.opportunities.apply', $event), ['message' => 'Hello'])
            ->assertForbidden();

        $this->assertDatabaseMissing('event_applications', [
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_volunteer_cannot_join_when_application_required_without_approval(): void
    {
        $user = $this->volunteerUser();
        $event = $this->futureEventWithApplicationRequired();

        $this->actingAs($user)->post(route('volunteer.opportunities.join', $event))->assertForbidden();
        $this->assertFalse($event->fresh()->userIsOnRoster($user));
    }

    public function test_volunteer_can_apply_and_join_after_admin_approval(): void
    {
        $user = $this->volunteerUser();
        $event = $this->futureEventWithApplicationRequired();

        $this->actingAs($user)
            ->post(route('volunteer.opportunities.apply', $event), ['message' => 'I would like to help.'])
            ->assertRedirect(route('volunteer.opportunities.show', array_merge(['event' => $event], PublicLocale::queryForUser($user))));

        $this->assertDatabaseHas('event_applications', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $applicationId = (int) EventApplication::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->value('id');

        $this->assertTrue(
            Audit::query()
                ->where('auditable_type', EventApplication::class)
                ->where('auditable_id', $applicationId)
                ->where('event', 'created')
                ->where('user_id', $user->id)
                ->exists()
        );

        $this->actingAs($user)->post(route('volunteer.opportunities.join', $event))->assertForbidden();

        EventApplication::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->update(['status' => EventApplication::STATUS_APPROVED]);

        $this->actingAs($user)->post(route('volunteer.opportunities.join', $event))->assertRedirect();
        $this->assertTrue($event->fresh()->userIsOnRoster($user));
    }

    public function test_volunteer_can_withdraw_pending_application(): void
    {
        $user = $this->volunteerUser();
        $event = $this->futureEventWithApplicationRequired();

        $this->actingAs($user)->post(route('volunteer.opportunities.apply', $event), []);

        $this->actingAs($user)
            ->post(route('volunteer.opportunities.withdraw-application', $event))
            ->assertRedirect(route('volunteer.opportunities.show', array_merge(['event' => $event], PublicLocale::queryForUser($user))));

        $this->assertDatabaseHas('event_applications', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => EventApplication::STATUS_WITHDRAWN,
        ]);
    }

    public function test_guest_cannot_post_apply(): void
    {
        $event = $this->futureEventWithApplicationRequired();

        $this->post(route('volunteer.opportunities.apply', $event), [])
            ->assertRedirect(route('login', ['lang' => 'en'], absolute: false));
    }
}

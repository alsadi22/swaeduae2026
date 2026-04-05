<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeImpactStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_impact_reflects_verified_attendance_and_organizations(): void
    {
        $this->seed(RoleSeeder::class);

        Organization::factory()->count(2)->create();

        $org = Organization::query()->firstOrFail();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'event_starts_at' => now()->subDays(2),
            'event_ends_at' => now()->subDay(),
        ]);

        $a = User::factory()->create();
        $a->assignRole('volunteer');
        $b = User::factory()->create();
        $b->assignRole('volunteer');

        Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $a->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subDays(2),
            'checked_out_at' => now()->subDays(2)->addMinutes(90),
            'minutes_worked' => 90,
        ]);
        Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $b->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subDays(2),
            'checked_out_at' => now()->subDays(2)->addMinutes(30),
            'minutes_worked' => 30,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        // 120 minutes → 2 rounded hours
        $response->assertSee('data-testid="impact-stat-hours">2', false);
        $response->assertSee('data-testid="impact-stat-volunteers">2', false);
        $response->assertSee('data-testid="impact-stat-partners">2', false);
        $response->assertSee('data-testid="impact-stat-events">1', false);
    }

    public function test_home_impact_counts_only_approved_organizations_as_partner_hosts(): void
    {
        Organization::factory()->create(['verification_status' => Organization::VERIFICATION_APPROVED]);
        Organization::factory()->pendingVerification()->create();

        $this->get('/')->assertOk()
            ->assertSee('data-testid="impact-stat-partners">1', false);
    }

    public function test_home_impact_sums_minutes_adjustments_into_verified_total(): void
    {
        $this->seed(RoleSeeder::class);

        Organization::factory()->create();

        $org = Organization::query()->firstOrFail();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'event_starts_at' => now()->subDays(2),
            'event_ends_at' => now()->subDay(),
        ]);

        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');

        Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subDays(2),
            'checked_out_at' => now()->subDays(2)->addMinutes(60),
            'minutes_worked' => 60,
            'minutes_adjustment' => 30,
        ]);

        $this->get('/')->assertOk()
            ->assertSee('data-testid="impact-stat-hours">2', false);
    }
}

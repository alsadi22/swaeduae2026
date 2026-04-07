<?php

namespace Tests\Feature\Admin;

use App\Models\CheckinAttempt;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCheckinAttemptIndexTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_admin_can_filter_checkin_attempts_by_volunteer_search(): void
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create();

        $matchUser = User::factory()->create(['name' => 'Zara CheckinSearch', 'email' => 'zara@example.test']);
        $otherUser = User::factory()->create(['name' => 'Yves Other', 'email' => 'yves@example.test']);

        CheckinAttempt::query()->create([
            'event_id' => $event->id,
            'user_id' => $matchUser->id,
            'attempt_type' => CheckinAttempt::TYPE_CHECK_IN,
            'latitude' => 1.0,
            'longitude' => 2.0,
            'outcome' => 'accepted',
            'created_at' => now(),
        ]);
        CheckinAttempt::query()->create([
            'event_id' => $event->id,
            'user_id' => $otherUser->id,
            'attempt_type' => CheckinAttempt::TYPE_CHECK_IN,
            'latitude' => 1.0,
            'longitude' => 2.0,
            'outcome' => 'accepted',
            'created_at' => now()->subSecond(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.checkin-attempts.index', [
            'search' => 'CheckinSearch',
        ]));

        $response->assertOk();
        $response->assertSee('Zara CheckinSearch', false);
        $response->assertDontSee('Yves Other', false);
    }

    public function test_admin_checkin_attempts_search_matches_email_with_underscore(): void
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create();

        $matchUser = User::factory()->create(['name' => 'Roster A', 'email' => 'vol_x@checkin.test']);
        $otherUser = User::factory()->create(['name' => 'Roster B', 'email' => 'other_y@checkin.test']);

        foreach ([$matchUser, $otherUser] as $user) {
            CheckinAttempt::query()->create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'attempt_type' => CheckinAttempt::TYPE_CHECK_IN,
                'latitude' => 1.0,
                'longitude' => 2.0,
                'outcome' => 'accepted',
                'created_at' => now(),
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.checkin-attempts.index', ['search' => 'vol_x']))
            ->assertOk()
            ->assertSee('Roster A', true)
            ->assertDontSee('Roster B', true);
    }

    public function test_admin_nav_shows_suspicious_checkin_badge_when_recent(): void
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create();
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');

        CheckinAttempt::query()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'attempt_type' => CheckinAttempt::TYPE_CHECK_IN,
            'latitude' => 1.0,
            'longitude' => 2.0,
            'outcome' => 'suspicious',
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.checkin-attempts.index'))
            ->assertOk()
            ->assertSee('data-testid="suspicious-checkin-attempts-nav-badge"', false);
    }

    public function test_admin_checkin_attempts_index_shows_export_and_copy_controls(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.checkin-attempts.index'))
            ->assertOk()
            ->assertSee('<title>'.e(__('Check-in log').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-testid="admin-checkin-attempts-export-csv"', false)
            ->assertSee('data-testid="admin-checkin-attempts-copy-filtered-url"', false);
    }

    public function test_admin_can_download_checkin_attempts_csv(): void
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create(['title_en' => 'CheckinCsvEvent']);
        $user = User::factory()->create(['email' => 'checkincsv@example.test']);

        $attempt = CheckinAttempt::query()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'attempt_type' => CheckinAttempt::TYPE_CHECK_IN,
            'latitude' => 1.0,
            'longitude' => 2.0,
            'outcome' => 'suspicious',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.checkin-attempts.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString((string) $attempt->id, $content);
        $this->assertStringContainsString('CheckinCsvEvent', $content);
        $this->assertStringContainsString('suspicious', $content);
    }

    public function test_volunteer_cannot_access_checkin_attempts_export(): void
    {
        $this->seed(RoleSeeder::class);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');

        $this->actingAs($volunteer)->get(route('admin.checkin-attempts.export'))->assertForbidden();
    }
}

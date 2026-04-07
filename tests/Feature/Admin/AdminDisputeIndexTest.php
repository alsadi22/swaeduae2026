<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\Dispute;
use App\Models\Event;
use App\Models\User;
use App\Support\PublicLocale;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDisputeIndexTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_admin_can_filter_disputes_by_volunteer_search(): void
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create(['title_en' => 'Shared Event']);

        $alice = User::factory()->create(['name' => 'Alice UniqueDispute', 'email' => 'alice@example.test']);
        $bob = User::factory()->create(['name' => 'Bob Other', 'email' => 'bob@example.test']);
        $alice->assignRole('volunteer');
        $bob->assignRole('volunteer');

        $attAlice = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $alice->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
        ]);
        $attBob = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $bob->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
        ]);

        Dispute::query()->create([
            'attendance_id' => $attAlice->id,
            'opened_by_user_id' => $alice->id,
            'status' => Dispute::STATUS_OPEN,
            'description' => str_repeat('a', 25).' alice dispute',
        ]);
        Dispute::query()->create([
            'attendance_id' => $attBob->id,
            'opened_by_user_id' => $bob->id,
            'status' => Dispute::STATUS_OPEN,
            'description' => str_repeat('b', 25).' bob dispute',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.disputes.index', [
            'search' => 'UniqueDispute',
        ]));

        $response->assertOk();
        $response->assertSee('Alice UniqueDispute', false);
        $response->assertDontSee('Bob Other', false);
    }

    public function test_admin_can_filter_disputes_by_event_id(): void
    {
        $admin = $this->adminUser();
        $eventA = Event::factory()->create(['title_en' => 'Event Alpha Dispute']);
        $eventB = Event::factory()->create(['title_en' => 'Event Beta Dispute']);

        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $attA = Attendance::query()->create([
            'event_id' => $eventA->id,
            'user_id' => $user->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
        ]);
        $attB = Attendance::query()->create([
            'event_id' => $eventB->id,
            'user_id' => $user->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
        ]);

        Dispute::query()->create([
            'attendance_id' => $attA->id,
            'opened_by_user_id' => $user->id,
            'status' => Dispute::STATUS_OPEN,
            'description' => str_repeat('x', 25).' a',
        ]);
        Dispute::query()->create([
            'attendance_id' => $attB->id,
            'opened_by_user_id' => $user->id,
            'status' => Dispute::STATUS_OPEN,
            'description' => str_repeat('y', 25).' b',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.disputes.index', [
            'event_id' => $eventA->id,
        ]));

        $response->assertOk();
        $response->assertSee('Event Alpha Dispute', false);
        // "Event Beta Dispute" still appears in the event filter `<select>`; table should list only Alpha.
        $this->assertSame(1, substr_count($response->getContent(), 'Event Beta Dispute'));
    }

    public function test_invalid_dispute_event_id_shows_validation_error(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->from(route('admin.disputes.index', PublicLocale::query()))
            ->get(route('admin.disputes.index', PublicLocale::mergeQuery(['event_id' => 999999])))
            ->assertRedirect(route('admin.disputes.index', PublicLocale::query()))
            ->assertSessionHasErrors('event_id');
    }

    public function test_admin_nav_shows_open_disputes_badge_count(): void
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create();

        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $attendance = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
        ]);

        Dispute::query()->create([
            'attendance_id' => $attendance->id,
            'opened_by_user_id' => $user->id,
            'status' => Dispute::STATUS_OPEN,
            'description' => str_repeat('o', 25).' open for badge',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.disputes.index'))
            ->assertOk()
            ->assertSee('data-testid="open-disputes-nav-badge"', false);
    }

    public function test_admin_disputes_index_shows_export_and_copy_controls(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.disputes.index'))
            ->assertOk()
            ->assertSee('<title>'.e(__('Disputes').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-testid="admin-disputes-export-csv"', false)
            ->assertSee('data-testid="admin-disputes-copy-filtered-url"', false);
    }

    public function test_admin_can_download_disputes_csv(): void
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create(['title_en' => 'DisputeCsvEventX']);
        $user = User::factory()->create(['name' => 'DisputeCsvVolunteerY', 'email' => 'disputecsv@example.test']);
        $user->assignRole('volunteer');

        $attendance = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
        ]);

        $dispute = Dispute::query()->create([
            'attendance_id' => $attendance->id,
            'opened_by_user_id' => $user->id,
            'status' => Dispute::STATUS_OPEN,
            'description' => 'UniqueDisputeCsvBodyMarkerZ',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.disputes.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString((string) $dispute->id, $content);
        $this->assertStringContainsString('DisputeCsvEventX', $content);
        $this->assertStringContainsString('UniqueDisputeCsvBodyMarkerZ', $content);
    }

    public function test_volunteer_cannot_access_disputes_export(): void
    {
        $this->seed(RoleSeeder::class);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');

        $this->actingAs($volunteer)->get(route('admin.disputes.export'))->assertForbidden();
    }

    public function test_admin_dispute_show_includes_copy_page_url_control(): void
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $attendance = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
        ]);

        $dispute = Dispute::query()->create([
            'attendance_id' => $attendance->id,
            'opened_by_user_id' => $user->id,
            'status' => Dispute::STATUS_OPEN,
            'description' => str_repeat('d', 25).' show page copy control.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.disputes.show', array_merge(['dispute' => $dispute], PublicLocale::query())))
            ->assertOk()
            ->assertSee('<title>'.e(__('Dispute detail').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-testid="admin-dispute-show-copy-page-url"', false)
            ->assertSee(str_repeat('d', 25).' show page copy control.', false);
    }
}

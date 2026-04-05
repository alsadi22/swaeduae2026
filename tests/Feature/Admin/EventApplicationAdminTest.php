<?php

namespace Tests\Feature\Admin;

use App\Mail\EventApplicationReviewedMail;
use App\Models\Event;
use App\Models\EventApplication;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use OwenIt\Auditing\Models\Audit;
use Tests\TestCase;

class EventApplicationAdminTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_guest_redirected_from_event_applications_index(): void
    {
        $this->get('/admin/event-applications')->assertRedirect(route('admin.login'));
    }

    public function test_volunteer_cannot_access_event_applications(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)->get('/admin/event-applications')->assertForbidden();
    }

    public function test_admin_can_view_event_applications_index(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)->get('/admin/event-applications')->assertOk();
    }

    public function test_admin_can_approve_pending_application(): void
    {
        Mail::fake();

        $admin = $this->adminUser();
        $org = Organization::factory()->create();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $app = EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $index = route('admin.event-applications.index');

        $this->actingAs($admin)
            ->from($index)
            ->post(route('admin.event-applications.approve', $app))
            ->assertRedirect($index);

        $this->assertEquals(EventApplication::STATUS_APPROVED, $app->fresh()->status);

        Mail::assertQueued(EventApplicationReviewedMail::class, function (EventApplicationReviewedMail $mail) use ($volunteer, $app): bool {
            return $mail->hasTo($volunteer->email)
                && $mail->outcome === EventApplicationReviewedMail::OUTCOME_APPROVED
                && $mail->application->id === $app->id;
        });

        $this->assertTrue(
            Audit::query()
                ->where('auditable_type', EventApplication::class)
                ->where('auditable_id', $app->id)
                ->where('event', 'updated')
                ->where('user_id', $admin->id)
                ->exists()
        );
    }

    public function test_admin_can_reject_pending_application(): void
    {
        Mail::fake();

        $admin = $this->adminUser();
        $org = Organization::factory()->create();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $app = EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $index = route('admin.event-applications.index');

        $this->actingAs($admin)
            ->from($index)
            ->post(route('admin.event-applications.reject', $app))
            ->assertRedirect($index);

        $this->assertEquals(EventApplication::STATUS_REJECTED, $app->fresh()->status);

        Mail::assertQueued(EventApplicationReviewedMail::class, function (EventApplicationReviewedMail $mail) use ($volunteer): bool {
            return $mail->hasTo($volunteer->email)
                && $mail->outcome === EventApplicationReviewedMail::OUTCOME_REJECTED;
        });
    }

    public function test_admin_reject_stores_optional_review_note(): void
    {
        Mail::fake();

        $admin = $this->adminUser();
        $org = Organization::factory()->create();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $app = EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $note = 'Please complete orientation before re-applying.';

        $index = route('admin.event-applications.index');

        $this->actingAs($admin)
            ->from($index)
            ->post(route('admin.event-applications.reject', $app), ['review_note' => $note])
            ->assertRedirect($index);

        $this->assertDatabaseHas('event_applications', [
            'id' => $app->id,
            'status' => EventApplication::STATUS_REJECTED,
            'review_note' => $note,
        ]);
    }

    public function test_admin_approve_clears_review_note(): void
    {
        Mail::fake();

        $admin = $this->adminUser();
        $org = Organization::factory()->create();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $app = EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);
        $app->forceFill(['review_note' => 'Stale note'])->save();

        $index = route('admin.event-applications.index');

        $this->actingAs($admin)
            ->from($index)
            ->post(route('admin.event-applications.approve', $app))
            ->assertRedirect($index);

        $this->assertNull($app->fresh()->review_note);
    }

    public function test_rejection_email_body_includes_review_note(): void
    {
        Mail::fake();

        $admin = $this->adminUser();
        $org = Organization::factory()->create();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $app = EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $note = 'Unique mail inclusion token xyz789';

        $this->actingAs($admin)
            ->from(route('admin.event-applications.index'))
            ->post(route('admin.event-applications.reject', $app), ['review_note' => $note]);

        Mail::assertQueued(EventApplicationReviewedMail::class, function (EventApplicationReviewedMail $mail) use ($note): bool {
            return str_contains($mail->render(), $note);
        });
    }

    public function test_admin_nav_shows_pending_applications_badge_count(): void
    {
        $admin = $this->adminUser();
        $org = Organization::factory()->create();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $u1 = User::factory()->create();
        $u1->assignRole('volunteer');
        $u2 = User::factory()->create();
        $u2->assignRole('volunteer');
        EventApplication::factory()->create(['event_id' => $event->id, 'user_id' => $u1->id, 'status' => EventApplication::STATUS_PENDING]);
        EventApplication::factory()->create(['event_id' => $event->id, 'user_id' => $u2->id, 'status' => EventApplication::STATUS_PENDING]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('data-testid="pending-event-applications-badge"', false);
        $response->assertSee('>2</span>', false);
    }

    public function test_admin_can_filter_applications_by_status(): void
    {
        $admin = $this->adminUser();
        $org = Organization::factory()->create();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $vPending = User::factory()->create(['email' => 'pending-only@example.com']);
        $vPending->assignRole('volunteer');
        $vRejected = User::factory()->create(['email' => 'rejected-only@example.com']);
        $vRejected->assignRole('volunteer');
        EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $vPending->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);
        EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $vRejected->id,
            'status' => EventApplication::STATUS_REJECTED,
        ]);

        $url = route('admin.event-applications.index', ['status' => EventApplication::STATUS_PENDING]);

        $this->actingAs($admin)->get($url)
            ->assertOk()
            ->assertSee('pending-only@example.com', false)
            ->assertDontSee('rejected-only@example.com', false);
    }

    public function test_admin_can_filter_applications_by_event(): void
    {
        $admin = $this->adminUser();
        $org = Organization::factory()->create();
        $eventA = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Alpha Event Filter',
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $eventB = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Beta Event Filter',
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $v = User::factory()->create(['email' => 'only-alpha-event@example.com']);
        $v->assignRole('volunteer');
        EventApplication::factory()->create([
            'event_id' => $eventA->id,
            'user_id' => $v->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);
        $v2 = User::factory()->create(['email' => 'only-beta-event@example.com']);
        $v2->assignRole('volunteer');
        EventApplication::factory()->create([
            'event_id' => $eventB->id,
            'user_id' => $v2->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.event-applications.index', ['event_id' => $eventA->id]))
            ->assertOk()
            ->assertSee('only-alpha-event@example.com', false)
            ->assertDontSee('only-beta-event@example.com', false);
    }

    public function test_invalid_application_filter_status_shows_validation_error(): void
    {
        $admin = $this->adminUser();
        $index = route('admin.event-applications.index');

        $this->actingAs($admin)
            ->from($index)
            ->get(route('admin.event-applications.index', ['status' => 'not-a-status']))
            ->assertRedirect($index)
            ->assertSessionHasErrors('status');
    }

    public function test_admin_applications_index_search_filters_by_volunteer_name(): void
    {
        $admin = $this->adminUser();
        $org = Organization::factory()->create();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $vMatch = User::factory()->create(['name' => 'ZetaUniqueVolunteerName', 'email' => 'zeta@example.com']);
        $vMatch->assignRole('volunteer');
        $vOther = User::factory()->create(['name' => 'Other Person', 'email' => 'otherperson@example.com']);
        $vOther->assignRole('volunteer');
        EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $vMatch->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);
        EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $vOther->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.event-applications.index', ['search' => 'ZetaUniqueVolunteer']))
            ->assertOk()
            ->assertSee('ZetaUniqueVolunteerName', false)
            ->assertDontSee('otherperson@example.com', false);
    }

    public function test_admin_applications_index_search_matches_email_with_underscore(): void
    {
        $admin = $this->adminUser();
        $org = Organization::factory()->create();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);
        $vMatch = User::factory()->create(['name' => 'Underscore Search A', 'email' => 'vol_user_x@example.com']);
        $vMatch->assignRole('volunteer');
        $vOther = User::factory()->create(['name' => 'Underscore Search B', 'email' => 'other_y@example.com']);
        $vOther->assignRole('volunteer');
        EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $vMatch->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);
        EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $vOther->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.event-applications.index', ['search' => 'vol_user']))
            ->assertOk()
            ->assertSee('vol_user_x@example.com', false)
            ->assertDontSee('other_y@example.com', false);
    }

    public function test_admin_applications_index_search_max_length_validation(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.event-applications.index', ['search' => str_repeat('a', 101)]))
            ->assertSessionHasErrors('search');
    }

    public function test_admin_applications_index_sort_oldest_submitted_first_orders_by_created_at(): void
    {
        $admin = $this->adminUser();
        $org = Organization::factory()->create();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'application_required' => true,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $vOld = User::factory()->create(['name' => 'AdminOlderAppSortZ']);
        $vOld->assignRole('volunteer');
        $vNew = User::factory()->create(['name' => 'AdminNewerAppSortY']);
        $vNew->assignRole('volunteer');

        $appOld = EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $vOld->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);
        $appNew = EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $vNew->id,
            'status' => EventApplication::STATUS_PENDING,
        ]);
        DB::table('event_applications')->where('id', $appOld->id)->update(['created_at' => now()->subDays(3)]);
        DB::table('event_applications')->where('id', $appNew->id)->update(['created_at' => now()->subDay()]);

        $this->actingAs($admin)
            ->get(route('admin.event-applications.index', ['sort' => 'submitted_asc']))
            ->assertOk()
            ->assertSeeInOrder(['AdminOlderAppSortZ', 'AdminNewerAppSortY'], false);
    }

    public function test_admin_applications_index_invalid_sort_returns_validation_error(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.event-applications.index', ['sort' => 'bogus']))
            ->assertSessionHasErrors('sort');
    }
}

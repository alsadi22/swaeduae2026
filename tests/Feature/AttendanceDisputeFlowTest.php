<?php

namespace Tests\Feature;

use App\Mail\DisputeOpenedStaffMail;
use App\Mail\DisputeResolvedVolunteerMail;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Dispute;
use App\Models\Event;
use App\Models\User;
use App\Support\PublicLocale;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AttendanceDisputeFlowTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_volunteer_can_submit_dispute_for_own_checked_out_attendance(): void
    {
        Mail::fake();
        Config::set('swaeduae.mail.staff_disputes', null);

        $this->seed(RoleSeeder::class);
        $event = Event::factory()->create();
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $attendance = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
            'minutes_worked' => 60,
        ]);

        $this->actingAs($volunteer)
            ->post(route('dashboard.attendance.disputes.store', $attendance), [
                'description' => str_repeat('a', 25).' problem with my hours.',
            ])
            ->assertRedirect(route('dashboard.attendance.index', ['lang' => 'en'], absolute: false))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('disputes', [
            'attendance_id' => $attendance->id,
            'opened_by_user_id' => $volunteer->id,
            'status' => Dispute::STATUS_OPEN,
        ]);

        $this->assertTrue(
            AttendanceLog::query()
                ->where('attendance_id', $attendance->id)
                ->where('action', 'dispute_opened')
                ->where('actor_user_id', $volunteer->id)
                ->exists()
        );

        Mail::assertNotQueued(DisputeOpenedStaffMail::class);
    }

    public function test_volunteer_dispute_submission_queues_staff_mail_when_staff_address_configured(): void
    {
        Mail::fake();
        Config::set('swaeduae.mail.staff_disputes', 'ops@example.test');

        $this->seed(RoleSeeder::class);
        $event = Event::factory()->create();
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $attendance = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
            'minutes_worked' => 60,
        ]);

        $this->actingAs($volunteer)
            ->post(route('dashboard.attendance.disputes.store', $attendance), [
                'description' => str_repeat('a', 25).' staff mail dispute.',
            ])
            ->assertRedirect(route('dashboard.attendance.index', ['lang' => 'en'], absolute: false));

        Mail::assertQueued(DisputeOpenedStaffMail::class, function (DisputeOpenedStaffMail $mail): bool {
            return $mail->hasTo('ops@example.test');
        });
    }

    public function test_volunteer_cannot_submit_second_open_dispute(): void
    {
        $this->seed(RoleSeeder::class);
        $event = Event::factory()->create();
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $attendance = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
        ]);

        Dispute::query()->create([
            'attendance_id' => $attendance->id,
            'opened_by_user_id' => $volunteer->id,
            'status' => Dispute::STATUS_OPEN,
            'description' => str_repeat('x', 25),
        ]);

        $this->actingAs($volunteer)
            ->post(route('dashboard.attendance.disputes.store', $attendance), [
                'description' => str_repeat('b', 25).' another try.',
            ])
            ->assertForbidden();
    }

    public function test_volunteer_gets_404_for_other_users_attendance(): void
    {
        $this->seed(RoleSeeder::class);
        $event = Event::factory()->create();
        $owner = User::factory()->create();
        $owner->assignRole('volunteer');
        $intruder = User::factory()->create();
        $intruder->assignRole('volunteer');
        $event->volunteers()->attach($owner->id);

        $attendance = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $owner->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
        ]);

        $this->actingAs($intruder)
            ->get(route('dashboard.attendance.disputes.create', $attendance))
            ->assertNotFound();
    }

    public function test_admin_can_resolve_dispute(): void
    {
        Mail::fake();

        $admin = $this->adminUser();
        $event = Event::factory()->create();
        $volunteer = User::factory()->create(['email' => 'vol-dispute-mail@example.test']);
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $attendance = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
        ]);

        $dispute = Dispute::query()->create([
            'attendance_id' => $attendance->id,
            'opened_by_user_id' => $volunteer->id,
            'status' => Dispute::STATUS_OPEN,
            'description' => str_repeat('c', 25),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.disputes.resolve', $dispute), [
                'resolution' => Dispute::STATUS_RESOLVED,
                'resolution_note' => 'Verified with coordinator.',
            ])
            ->assertRedirect(route('admin.disputes.show', array_merge(['dispute' => $dispute], PublicLocale::query())))
            ->assertSessionHas('status');

        $dispute->refresh();
        $this->assertSame(Dispute::STATUS_RESOLVED, $dispute->status);
        $this->assertNotNull($dispute->resolved_at);
        $this->assertSame($admin->id, $dispute->resolved_by_user_id);

        $this->assertTrue(
            AttendanceLog::query()
                ->where('attendance_id', $attendance->id)
                ->where('action', 'dispute_resolved')
                ->where('actor_user_id', $admin->id)
                ->exists()
        );

        Mail::assertQueued(DisputeResolvedVolunteerMail::class, function (DisputeResolvedVolunteerMail $mail) use ($dispute, $volunteer): bool {
            return $mail->dispute->is($dispute->fresh())
                && $mail->hasTo($volunteer->email);
        });
    }

    public function test_dispute_resolved_email_links_include_volunteer_preferred_locale(): void
    {
        Mail::fake();

        $admin = $this->adminUser();
        $event = Event::factory()->create();
        $volunteer = User::factory()->create([
            'email' => 'vol-ar-dispute@example.test',
            'locale_preferred' => 'ar',
        ]);
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $attendance = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
        ]);

        $dispute = Dispute::query()->create([
            'attendance_id' => $attendance->id,
            'opened_by_user_id' => $volunteer->id,
            'status' => Dispute::STATUS_OPEN,
            'description' => str_repeat('c', 25),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.disputes.resolve', $dispute), [
                'resolution' => Dispute::STATUS_RESOLVED,
                'resolution_note' => 'Done.',
            ])
            ->assertRedirect(route('admin.disputes.show', array_merge(['dispute' => $dispute], PublicLocale::query())));

        Mail::assertQueued(DisputeResolvedVolunteerMail::class, function (DisputeResolvedVolunteerMail $mail) use ($volunteer): bool {
            if (! $mail->hasTo($volunteer->email)) {
                return false;
            }

            $html = $mail->render();

            return str_contains($html, 'lang=ar');
        });
    }

    public function test_volunteer_cannot_access_admin_disputes(): void
    {
        $this->seed(RoleSeeder::class);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');

        $this->actingAs($volunteer)->get('/admin/disputes')->assertForbidden();
    }

    public function test_volunteer_dispute_create_form_includes_copy_page_url_control(): void
    {
        $this->seed(RoleSeeder::class);
        $event = Event::factory()->create(['title_en' => 'Dispute Form Event Unique']);
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $attendance = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
        ]);

        $this->actingAs($volunteer)
            ->get(route('dashboard.attendance.disputes.create', $attendance))
            ->assertOk()
            ->assertSee('data-testid="volunteer-dispute-create-copy-page-url"', false)
            ->assertSee('<title>'.e(__('Open dispute').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('Dispute Form Event Unique', false);
    }

    public function test_volunteer_dispute_create_shows_validation_errors_in_live_region(): void
    {
        $this->seed(RoleSeeder::class);
        $event = Event::factory()->create();
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $attendance = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
        ]);

        $this->actingAs($volunteer)
            ->from(route('dashboard.attendance.disputes.create', $attendance))
            ->followingRedirects()
            ->post(route('dashboard.attendance.disputes.store', $attendance), [
                'description' => 'too short',
            ])
            ->assertOk()
            ->assertSee('data-testid="volunteer-dispute-create-validation-errors"', false);
    }
}

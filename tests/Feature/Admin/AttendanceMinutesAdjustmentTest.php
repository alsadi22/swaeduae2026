<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Event;
use App\Models\User;
use App\Support\PublicLocale;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceMinutesAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_admin_can_save_minutes_adjustment_and_volunteer_dashboard_reflects_verified_total(): void
    {
        $admin = $this->adminUser();
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
            'minutes_worked' => 100,
            'suspicion_flags' => ['low_gps_accuracy'],
        ]);

        $this->actingAs($admin)->post(
            route('admin.attendances.minutes-adjustment.update', $attendance),
            [
                'minutes_adjustment' => 30,
                'minutes_adjustment_note' => 'Coordinator confirmed late stay.',
            ]
        )->assertRedirect(route('admin.flagged-attendance.index', PublicLocale::query(), false));

        $attendance->refresh();
        $this->assertSame(30, $attendance->minutes_adjustment);
        $this->assertSame(130, $attendance->verifiedMinutes());

        $this->assertDatabaseHas('attendance_logs', [
            'attendance_id' => $attendance->id,
            'action' => 'minutes_adjustment',
        ]);

        $log = AttendanceLog::query()
            ->where('attendance_id', $attendance->id)
            ->where('action', 'minutes_adjustment')
            ->first();
        $this->assertNotNull($log);
        $this->assertSame(130, $log->payload['new_verified_minutes'] ?? null);

        $this->actingAs($volunteer)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('class="sr-only">130</span>', false);
    }

    public function test_volunteer_cannot_post_minutes_adjustment(): void
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
            'minutes_worked' => 50,
            'suspicion_flags' => ['outside_geofence'],
        ]);

        $this->actingAs($volunteer)->post(
            route('admin.attendances.minutes-adjustment.update', $attendance),
            ['minutes_adjustment' => 10]
        )->assertForbidden();
    }

    public function test_admin_cannot_adjust_checked_in_attendance(): void
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create();
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event->volunteers()->attach($volunteer->id);

        $attendance = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'state' => Attendance::STATE_CHECKED_IN,
            'checked_in_at' => now()->subMinutes(30),
            'suspicion_flags' => ['low_gps_accuracy'],
        ]);

        $this->actingAs($admin)->post(
            route('admin.attendances.minutes-adjustment.update', $attendance),
            ['minutes_adjustment' => 10]
        )->assertForbidden();
    }
}

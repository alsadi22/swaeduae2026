<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\CheckinAttempt;
use App\Models\Dispute;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDataExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_download_account_json_export(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');
        $user->markEmailAsVerified();

        $response = $this->actingAs($user)->get(route('profile.data-export'));

        $response->assertOk();
        $response->assertHeader('content-disposition');
        $data = $response->json();
        $this->assertSame('swaeduae.account_export', $data['schema']);
        $this->assertSame(3, $data['schema_version']);
        $this->assertArrayHasKey('user', $data);
        $this->assertSame($user->email, $data['user']['email']);
        $this->assertContains('volunteer', $data['user']['roles']);
        $this->assertArrayHasKey('event_applications', $data);
        $this->assertArrayHasKey('attendances', $data);
        $this->assertArrayHasKey('disputes', $data);
        $this->assertSame([], $data['disputes']);
        $this->assertArrayHasKey('checkin_attempts_summary', $data);
        $this->assertSame([], $data['checkin_attempts_summary']);
    }

    public function test_unverified_user_cannot_access_data_export(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)->get(route('profile.data-export'))->assertRedirect();
    }

    public function test_guest_cannot_access_data_export(): void
    {
        $this->get(route('profile.data-export'))->assertRedirect(route('login'));
    }

    public function test_data_export_is_throttled_per_user(): void
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($user)->get(route('profile.data-export'))->assertOk();
        }

        $this->actingAs($user)->get(route('profile.data-export'))->assertStatus(429);
    }

    public function test_data_export_includes_disputes_for_volunteer(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');
        $user->markEmailAsVerified();

        $event = Event::factory()->create();
        $event->volunteers()->attach($user->id);
        $attendance = Attendance::query()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'state' => Attendance::STATE_CHECKED_OUT,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
            'minutes_worked' => 30,
        ]);
        Dispute::query()->create([
            'attendance_id' => $attendance->id,
            'opened_by_user_id' => $user->id,
            'status' => Dispute::STATUS_OPEN,
            'description' => str_repeat('x', 25).' export dispute',
        ]);

        $data = $this->actingAs($user)->get(route('profile.data-export'))->assertOk()->json();

        $this->assertSame(3, $data['schema_version']);
        $this->assertCount(1, $data['disputes']);
        $this->assertSame(Dispute::STATUS_OPEN, $data['disputes'][0]['status']);
        $this->assertSame($event->uuid, $data['disputes'][0]['event']['uuid']);
    }

    public function test_data_export_includes_checkin_attempts_summary_counts(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');
        $user->markEmailAsVerified();

        $event = Event::factory()->create();
        foreach (['suspicious', 'suspicious', 'accepted'] as $outcome) {
            CheckinAttempt::query()->create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'attempt_type' => CheckinAttempt::TYPE_CHECK_IN,
                'latitude' => 1.0,
                'longitude' => 2.0,
                'outcome' => $outcome,
                'created_at' => now(),
            ]);
        }

        $data = $this->actingAs($user)->get(route('profile.data-export'))->assertOk()->json();

        $this->assertSame(3, $data['schema_version']);
        $this->assertSame(2, $data['checkin_attempts_summary']['suspicious']);
        $this->assertSame(1, $data['checkin_attempts_summary']['accepted']);
    }
}

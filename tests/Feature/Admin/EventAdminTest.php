<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use App\Support\AttendanceCheckpointUrl;
use App\Support\PublicLocale;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class EventAdminTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoles(): void
    {
        $this->seed(RoleSeeder::class);
    }

    private function adminUser(): User
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    /** @return array<string, mixed> */
    private function validEventPayload(Organization $org): array
    {
        $eventStart = now()->addDays(2)->startOfHour();

        return [
            'organization_id' => $org->id,
            'capacity' => null,
            'application_required' => '0',
            'title_en' => 'Admin-managed event',
            'title_ar' => null,
            'latitude' => 25.2048,
            'longitude' => 55.2708,
            'geofence_radius_meters' => 200,
            'geofence_strict' => '0',
            'min_gps_accuracy_meters' => 100,
            'checkin_window_starts_at' => $eventStart->copy()->subHour()->format('Y-m-d\TH:i'),
            'checkin_window_ends_at' => $eventStart->copy()->addHours(3)->format('Y-m-d\TH:i'),
            'event_starts_at' => $eventStart->format('Y-m-d\TH:i'),
            'event_ends_at' => $eventStart->copy()->addHours(2)->format('Y-m-d\TH:i'),
            'checkout_grace_minutes_after_event' => 30,
        ];
    }

    public function test_guest_redirected_from_admin_events_index(): void
    {
        $this->get('/admin/events')->assertRedirect(route('admin.login'));
    }

    public function test_volunteer_cannot_access_admin_events(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)->get('/admin/events')->assertForbidden();
    }

    public function test_admin_can_view_events_index(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get('/admin/events')
            ->assertOk()
            ->assertSee('data-testid="admin-events-copy-filtered-url"', false)
            ->assertSee('data-testid="admin-events-export-csv"', false)
            ->assertSee('<title>'.e(__('Admin events').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false);
    }

    public function test_admin_can_download_events_csv(): void
    {
        $user = $this->adminUser();
        $org = Organization::factory()->create(['name_en' => 'CsvOrgUniqueName']);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'CsvEventTitleUnique',
            'title_ar' => 'عنوان',
        ]);

        $response = $this->actingAs($user)->get(route('admin.events.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString((string) $event->id, $content);
        $this->assertStringContainsString('CsvEventTitleUnique', $content);
        $this->assertStringContainsString('CsvOrgUniqueName', $content);
    }

    public function test_volunteer_cannot_access_admin_events_export(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)->get(route('admin.events.export'))->assertForbidden();
    }

    public function test_admin_can_create_event_with_capacity(): void
    {
        $user = $this->adminUser();
        $org = Organization::factory()->create();
        $payload = $this->validEventPayload($org);
        $payload['capacity'] = 25;

        $response = $this->actingAs($user)->post('/admin/events', $payload);

        $response->assertRedirect(route('admin.events.index', PublicLocale::query()));
        $this->actingAs($user)
            ->get(route('admin.events.index', PublicLocale::query()))
            ->assertOk()
            ->assertSee('data-testid="admin-events-flash-status"', false)
            ->assertSee(__('Event created.'), false);
        $this->assertDatabaseHas('events', [
            'organization_id' => $org->id,
            'title_en' => 'Admin-managed event',
            'capacity' => 25,
        ]);
    }

    public function test_admin_can_update_event(): void
    {
        $user = $this->adminUser();
        $event = Event::factory()->create(['title_en' => 'Old title']);
        $payload = $this->validEventPayload($event->organization);
        $payload['title_en'] = 'Updated title';

        $response = $this->actingAs($user)
            ->put('/admin/events/'.$event->uuid, $payload);

        $response->assertRedirect(route('admin.events.index', PublicLocale::query()));
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title_en' => 'Updated title',
        ]);
    }

    public function test_admin_can_delete_event(): void
    {
        $user = $this->adminUser();
        $event = Event::factory()->create();

        $response = $this->actingAs($user)
            ->delete('/admin/events/'.$event->uuid);

        $response->assertRedirect(route('admin.events.index', PublicLocale::query()));
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_validation_rejects_checkin_window_ending_before_event_start(): void
    {
        $user = $this->adminUser();
        $org = Organization::factory()->create();
        $eventStart = now()->addDays(2)->startOfHour();
        $payload = [
            'organization_id' => $org->id,
            'capacity' => null,
            'title_en' => 'Bad window',
            'title_ar' => null,
            'latitude' => 25.0,
            'longitude' => 55.0,
            'geofence_radius_meters' => 100,
            'geofence_strict' => '0',
            'min_gps_accuracy_meters' => 75,
            'checkin_window_starts_at' => $eventStart->copy()->subHours(3)->format('Y-m-d\TH:i'),
            'checkin_window_ends_at' => $eventStart->copy()->subHour()->format('Y-m-d\TH:i'),
            'event_starts_at' => $eventStart->format('Y-m-d\TH:i'),
            'event_ends_at' => $eventStart->copy()->addHour()->format('Y-m-d\TH:i'),
            'checkout_grace_minutes_after_event' => 30,
        ];

        $this->actingAs($user)->post('/admin/events', $payload)->assertSessionHasErrors('checkin_window_ends_at');
    }

    public function test_admin_can_generate_checkpoint_signed_url(): void
    {
        $user = $this->adminUser();
        $event = Event::factory()->create();

        $this->actingAs($user)->post(
            route('admin.events.checkpoint-signed-url', $event),
            ['days' => 14]
        )
            ->assertRedirect(route('admin.events.edit', array_merge(['event' => $event], PublicLocale::query())))
            ->assertSessionHas('checkpoint_signed_url');

        $url = session('checkpoint_signed_url');
        $this->assertIsString($url);
        $this->assertStringContainsString('/attendance/checkpoint/', $url);
        $this->assertStringContainsString('signature=', $url);
        $this->assertStringContainsString('expires=', $url);
    }

    public function test_volunteer_cannot_generate_checkpoint_signed_url(): void
    {
        $this->seedRoles();
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $event = Event::factory()->create();

        $this->actingAs($volunteer)->post(route('admin.events.checkpoint-signed-url', $event))
            ->assertForbidden();
    }

    public function test_rostered_volunteer_can_open_signed_checkpoint_url(): void
    {
        $this->seedRoles();
        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');
        $eventStart = now()->addDay();
        $event = Event::factory()->create([
            'event_starts_at' => $eventStart,
            'event_ends_at' => $eventStart->copy()->addHours(2),
            'checkin_window_starts_at' => now()->subHour(),
            'checkin_window_ends_at' => $eventStart->copy()->addDay(),
        ]);
        $event->volunteers()->attach($volunteer->id);

        $url = AttendanceCheckpointUrl::temporarySignedShowUrl($event, 1);

        $this->actingAs($volunteer)->get($url)->assertOk();
    }

    public function test_artisan_attendance_link_outputs_signed_url(): void
    {
        $event = Event::factory()->create();

        $exitCode = Artisan::call('swaeduae:attendance-link', ['event_uuid' => $event->uuid]);
        $this->assertSame(0, $exitCode);
        $output = trim(Artisan::output());
        $this->assertStringContainsString('/attendance/checkpoint/', $output);
        $this->assertStringContainsString((string) $event->uuid, $output);
    }

    public function test_admin_events_index_search_matches_organization_name(): void
    {
        $admin = $this->adminUser();
        $org = Organization::factory()->create(['name_en' => 'HostOrgUniqueSearchABC']);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Generic Event Title',
        ]);
        $otherOrg = Organization::factory()->create(['name_en' => 'Other Host']);
        Event::factory()->create([
            'organization_id' => $otherOrg->id,
            'title_en' => 'Another Event',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.index', ['search' => 'HostOrgUniqueSearch']))
            ->assertOk()
            ->assertSee('Generic Event Title', false)
            ->assertDontSee('Another Event', false);
    }

    public function test_admin_events_index_search_matches_title_with_underscore(): void
    {
        $admin = $this->adminUser();
        $org = Organization::factory()->create();
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Admin_Event_Title_UniqueUnderscore',
        ]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Plain Admin Event',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.index', ['search' => 'Event_Title_Unique']))
            ->assertOk()
            ->assertSee('Admin_Event_Title_UniqueUnderscore', false)
            ->assertDontSee('Plain Admin Event', false);
    }

    public function test_admin_events_index_search_max_length_validation(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.events.index', ['search' => str_repeat('b', 101)]))
            ->assertSessionHasErrors('search');
    }

    public function test_admin_event_edit_includes_copy_page_url_control(): void
    {
        $user = $this->adminUser();
        $org = Organization::factory()->create();
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'EditPageCopyUrlEvent',
        ]);

        $this->actingAs($user)
            ->get(route('admin.events.edit', array_merge(['event' => $event], PublicLocale::query())))
            ->assertOk()
            ->assertSee('<title>'.e(__('Edit event').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-testid="admin-event-edit-copy-page-url"', false);
    }

    public function test_admin_event_create_includes_copy_page_url_control(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.events.create', PublicLocale::query()))
            ->assertOk()
            ->assertSee('<title>'.e(__('New event').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-testid="admin-event-create-copy-page-url"', false);
    }
}

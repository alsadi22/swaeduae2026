<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceDemoSeeder extends Seeder
{
    /**
     * Local demo: one organization, one event, volunteer@example.com on roster (password: password).
     */
    public function run(): void
    {
        $org = Organization::query()->firstOrCreate(
            ['name_en' => 'SwaedUAE Demo Organization'],
            ['name_ar' => null]
        );

        $event = Event::query()->firstOrCreate(
            [
                'organization_id' => $org->id,
                'title_en' => 'Demo community event (attendance test)',
            ],
            [
                'title_ar' => null,
                'latitude' => 25.2048,
                'longitude' => 55.2708,
                'geofence_radius_meters' => 500,
                'geofence_strict' => false,
                'min_gps_accuracy_meters' => 150,
                'checkin_window_starts_at' => now()->subHours(2),
                'checkin_window_ends_at' => now()->addHours(6),
                'event_starts_at' => now()->subHour(),
                'event_ends_at' => now()->addHours(3),
                'checkout_grace_minutes_after_event' => 30,
            ]
        );

        $volunteer = User::query()->firstOrCreate(
            ['email' => 'volunteer@example.com'],
            [
                'name' => 'Demo Volunteer',
                'password' => 'password',
            ]
        );

        if (! $volunteer->hasRole('volunteer')) {
            $volunteer->assignRole('volunteer');
        }

        $event->volunteers()->syncWithoutDetaching([$volunteer->id]);
    }
}

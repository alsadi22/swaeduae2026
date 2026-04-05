<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $eventStart = now()->addHour();

        return [
            'organization_id' => Organization::factory(),
            'capacity' => null,
            'application_required' => false,
            'title_en' => fake()->sentence(3),
            'title_ar' => null,
            'latitude' => 25.2048,
            'longitude' => 55.2708,
            'geofence_radius_meters' => 500,
            'geofence_strict' => false,
            'min_gps_accuracy_meters' => 150,
            'checkin_window_starts_at' => now()->subHour(),
            'checkin_window_ends_at' => now()->addHours(4),
            'event_starts_at' => $eventStart,
            'event_ends_at' => (clone $eventStart)->addHours(2),
            'checkout_grace_minutes_after_event' => 30,
        ];
    }

    public function strictGeofence(): static
    {
        return $this->state(fn (array $attributes) => [
            'geofence_strict' => true,
        ]);
    }
}

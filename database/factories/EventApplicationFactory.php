<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventApplication>
 */
class EventApplicationFactory extends Factory
{
    protected $model = EventApplication::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'status' => EventApplication::STATUS_PENDING,
            'message' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventApplication::STATUS_APPROVED,
        ]);
    }
}

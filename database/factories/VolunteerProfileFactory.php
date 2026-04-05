<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VolunteerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VolunteerProfile>
 */
class VolunteerProfileFactory extends Factory
{
    protected $model = VolunteerProfile::class;

    public function definition(): array
    {
        return [
            'bio' => fake()->paragraphs(2, true),
            'skills' => implode(', ', fake()->words(5)),
            'availability' => fake()->sentence(),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => '+9715'.fake()->numerify('#######'),
            'notification_email_opt_in' => true,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'name_en' => fake()->company(),
            'name_ar' => null,
            'verification_status' => Organization::VERIFICATION_APPROVED,
            'verification_review_note' => null,
            'verification_reviewed_at' => null,
            'registered_by_user_id' => null,
        ];
    }

    public function pendingVerification(): static
    {
        return $this->state(fn () => [
            'verification_status' => Organization::VERIFICATION_PENDING,
            'verification_review_note' => null,
            'verification_reviewed_at' => null,
        ]);
    }
}

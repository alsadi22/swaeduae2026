<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OrganizationInvitation>
 */
class OrganizationInvitationFactory extends Factory
{
    protected $model = OrganizationInvitation::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'email' => fake()->unique()->safeEmail(),
            'role' => OrganizationInvitation::ROLE_COORDINATOR,
            'token_hash' => OrganizationInvitation::hashToken(Str::random(64)),
            'invited_by_user_id' => null,
            'expires_at' => now()->addDays(14),
            'accepted_at' => null,
        ];
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn () => ['organization_id' => $organization->id]);
    }

    public function invitedBy(User $user): static
    {
        return $this->state(fn () => ['invited_by_user_id' => $user->id]);
    }
}

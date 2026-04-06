<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VolunteerProfile;
use App\Support\PublicLocale;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolunteerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_volunteer_profile_edit(): void
    {
        $this->get(route('volunteer.profile.edit'))
            ->assertRedirect(route('login', ['lang' => 'en'], absolute: false));
    }

    public function test_guest_redirect_to_login_preserves_lang_query_on_volunteer_profile(): void
    {
        $this->get(route('volunteer.profile.edit', ['lang' => 'ar']))
            ->assertRedirect(route('login', ['lang' => 'ar'], absolute: false));
    }

    public function test_non_volunteer_cannot_access_volunteer_profile_edit(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('volunteer.profile.edit'))->assertForbidden();
    }

    public function test_volunteer_can_view_and_update_profile(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)
            ->get(route('volunteer.profile.edit'))
            ->assertOk()
            ->assertSee(__('Volunteer profile'), false)
            ->assertSee(__('Profile completion'), false);

        $bio = str_repeat('a', 20);

        $this->actingAs($user)
            ->patch(route('volunteer.profile.update'), [
                'bio' => $bio,
                'skills' => 'First aid',
                'availability' => 'Weekends',
                'emergency_contact_name' => 'Jane Doe',
                'emergency_contact_phone' => '+971501112233',
                'emirates_id_masked' => '***784',
                'notification_email_opt_in' => '1',
            ])
            ->assertRedirect(route('volunteer.profile.edit', PublicLocale::queryForUser($user)))
            ->assertSessionHas('status');

        $this->actingAs($user)
            ->get(route('volunteer.profile.edit'))
            ->assertOk()
            ->assertSee('data-testid="volunteer-profile-last-saved"', false);

        $this->assertTrue($user->fresh()->hasMinimumVolunteerProfileForCommitments());

        $profile = VolunteerProfile::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertSame($bio, $profile->bio);
        $this->assertTrue($profile->notification_email_opt_in);
    }

    public function test_verified_volunteer_sees_data_export_section_on_profile_edit(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');
        $user->markEmailAsVerified();

        $this->actingAs($user)
            ->get(route('volunteer.profile.edit'))
            ->assertOk()
            ->assertSee(__('Download my data (JSON)'), false)
            ->assertSee(__('Account data export hint'), false);
    }

    public function test_volunteer_profile_update_is_throttled_per_user(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $basePayload = [
            'skills' => 'First aid',
            'availability' => 'Weekends',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '+971501112233',
            'emirates_id_masked' => null,
            'notification_email_opt_in' => '0',
        ];

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)
                ->patch(route('volunteer.profile.update'), array_merge($basePayload, [
                    'bio' => str_repeat('a', 20).(string) $i,
                ]))
                ->assertRedirect(route('volunteer.profile.edit', PublicLocale::queryForUser($user)));
        }

        $this->actingAs($user)
            ->patch(route('volunteer.profile.update'), array_merge($basePayload, [
                'bio' => str_repeat('b', 22),
            ]))
            ->assertStatus(429);
    }
}

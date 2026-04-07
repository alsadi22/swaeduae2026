<?php

namespace Tests\Feature;

use App\Mail\DataErasureRequestedMail;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProfileErasureRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_post_erasure_request(): void
    {
        $this->post(route('profile.erasure-request'), ['message' => 'Please erase'])
            ->assertRedirect(route('login', ['lang' => 'en'], absolute: false));
    }

    public function test_unverified_user_cannot_post_erasure_request(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->unverified()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)
            ->post(route('profile.erasure-request'), ['message' => 'Test'])
            ->assertRedirect(route('verification.notice', absolute: false));
    }

    public function test_verified_user_can_submit_erasure_request_and_mail_is_queued(): void
    {
        Mail::fake();

        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->post(route('profile.erasure-request'), ['message' => 'Please delete my volunteer history.'])
            ->assertRedirect(route('profile.edit', ['lang' => 'en'], absolute: false))
            ->assertSessionHas('status', 'erasure-request-submitted');

        Mail::assertQueued(DataErasureRequestedMail::class, function (DataErasureRequestedMail $mail): bool {
            return $mail->optionalMessage === 'Please delete my volunteer history.';
        });

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('data-testid="profile-erasure-request-flash-success"', false);
    }

    public function test_volunteer_erasure_with_return_field_redirects_to_volunteer_profile(): void
    {
        Mail::fake();

        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)
            ->from(route('volunteer.profile.edit'))
            ->post(route('profile.erasure-request'), [
                'message' => 'From volunteer profile.',
                'return' => 'volunteer_profile',
            ])
            ->assertRedirect(route('volunteer.profile.edit', ['lang' => 'en'], absolute: false))
            ->assertSessionHas('status', 'erasure-request-submitted');

        Mail::assertQueued(DataErasureRequestedMail::class);

        $this->actingAs($user)
            ->get(route('volunteer.profile.edit'))
            ->assertOk()
            ->assertSee('data-testid="volunteer-profile-erasure-request-flash-success"', false);
    }

    public function test_non_volunteer_cannot_use_volunteer_profile_return_redirect(): void
    {
        Mail::fake();

        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post(route('profile.erasure-request'), [
                'return' => 'volunteer_profile',
            ])
            ->assertRedirect(route('profile.edit', ['lang' => 'en'], absolute: false));

        Mail::assertQueued(DataErasureRequestedMail::class);
    }

    public function test_erasure_request_is_throttled_per_day(): void
    {
        Mail::fake();

        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)->post(route('profile.erasure-request'), [])->assertSessionHas('status', 'erasure-request-submitted');
        $this->actingAs($user)->post(route('profile.erasure-request'), [])->assertSessionHas('status', 'erasure-request-submitted');
        $this->actingAs($user)->post(route('profile.erasure-request'), [])->assertStatus(429);

        Mail::assertQueued(DataErasureRequestedMail::class, 2);
    }
}

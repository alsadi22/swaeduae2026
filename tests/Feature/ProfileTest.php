<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PublicLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee('data-testid="profile-edit-copy-page-url"', false);
        $response->assertSee('data-testid="profile-data-privacy-copy-page-url"', false);
        $response->assertSee('data-testid="skip-to-main-content"', false);
        $response->assertSee('id="main-content"', false);
        $response->assertSee('<title>'.e(__('Profile').' — '.__('SwaedUAE')).'</title>', false);
        $response->assertSee('rel="manifest"', false);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit', PublicLocale::queryForUser($user)));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit', PublicLocale::queryForUser($user)));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('home', PublicLocale::queryForUser($user), absolute: false));

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->fresh());
    }

    public function test_profile_update_is_throttled_per_user(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)
                ->patch('/profile', [
                    'name' => 'User '.$i,
                    'email' => $user->email,
                ])
                ->assertRedirect(route('profile.edit', PublicLocale::queryForUser($user)));
        }

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'User Eleven',
                'email' => $user->email,
            ])
            ->assertStatus(429);
    }

    public function test_profile_update_redirect_preserves_explicit_lang_query_from_request(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update', ['lang' => 'ar']), [
                'name' => 'Arabic Context User',
                'email' => $user->email,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit', ['lang' => 'ar']));

        $this->assertSame('Arabic Context User', $user->fresh()->name);
    }
}

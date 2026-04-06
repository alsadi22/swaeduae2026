<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\PublicLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit', PublicLocale::queryForUser($user)));

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_password_update_redirect_preserves_explicit_lang_query_from_request(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('password.update', ['lang' => 'ar']), [
                'current_password' => 'password',
                'password' => 'new-password-ar',
                'password_confirmation' => 'new-password-ar',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit', ['lang' => 'ar']));

        $this->assertTrue(Hash::check('new-password-ar', $user->fresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect(route('profile.edit'));
    }

    public function test_password_update_is_throttled_per_user(): void
    {
        $user = User::factory()->create();
        $current = 'password';

        for ($i = 0; $i < 10; $i++) {
            $next = 'New-pass-'.$i.'-Xx1';
            $this->actingAs($user)
                ->from('/profile')
                ->put('/password', [
                    'current_password' => $current,
                    'password' => $next,
                    'password_confirmation' => $next,
                ])
                ->assertRedirect(route('profile.edit', PublicLocale::queryForUser($user)));
            $current = $next;
        }

        $this->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => $current,
                'password' => 'Another-pass-99-Xx1',
                'password_confirmation' => 'Another-pass-99-Xx1',
            ])
            ->assertStatus(429);
    }
}

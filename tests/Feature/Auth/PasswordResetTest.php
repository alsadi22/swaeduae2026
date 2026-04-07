<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('data-testid="forgot-password-copy-page-url"', false);
        $response->assertSee('autocomplete="username"', false);
        $response->assertSee('<title>'.e(__('Forgot password').' — '.__('SwaedUAE')).'</title>', false);
    }

    public function test_forgot_password_success_shows_accessible_flash(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])->assertRedirect();

        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('data-testid="auth-flash-status"', false);
    }

    public function test_forgot_password_includes_opportunities_footer_with_locale(): void
    {
        $oppAr = route('volunteer.opportunities.index', ['lang' => 'ar'], false);

        $this->get(route('password.request', ['lang' => 'ar']))
            ->assertOk()
            ->assertSee('data-testid="forgot-password-footer-opportunities"', false)
            ->assertSee($oppAr, false);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);
            $response->assertSee('data-testid="reset-password-copy-page-url"', false);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login', ['lang' => 'en'], absolute: false));

            return true;
        });
    }

    public function test_password_reset_redirect_to_login_preserves_lang_from_request(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post(route('password.store', ['lang' => 'ar']), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login', ['lang' => 'ar'], absolute: false));

            return true;
        });
    }

    public function test_forgot_password_post_is_throttled_per_ip(): void
    {
        Notification::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/forgot-password', ['email' => "pwd-throttle-{$i}@example.org"]);
        }

        $this->post('/forgot-password', ['email' => 'pwd-throttle-5@example.org'])
            ->assertStatus(429);
    }
}

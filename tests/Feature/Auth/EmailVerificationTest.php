<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
        $response->assertSee('data-testid="verify-email-copy-page-url"', false);
        $response->assertSee('<title>'.e(__('Verify your email').' — '.__('SwaedUAE')).'</title>', false);
        $response->assertSee('rel="manifest"', false);
    }

    public function test_email_verification_resend_shows_accessible_flash(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->from('/verify-email')
            ->post(route('verification.send'))
            ->assertRedirect('/verify-email');

        $this->actingAs($user)
            ->get('/verify-email')
            ->assertOk()
            ->assertSee('data-testid="verify-email-link-sent"', false);
    }

    public function test_verified_user_visiting_verify_email_redirects_to_dashboard_with_locale(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/verify-email')
            ->assertRedirect(route('dashboard', ['lang' => 'en'], absolute: false));
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', ['lang' => 'en', 'verified' => '1'], absolute: false));
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}

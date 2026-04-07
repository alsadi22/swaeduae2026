<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Support\AuthRedirect;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['swaeduae.security.admin_two_factor_required' => true]);
    }

    public function test_admin_without_two_factor_is_redirected_to_setup_when_accessing_cms(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('admin.cms-pages.index'))
            ->assertRedirect(route('admin.two-factor.setup', ['lang' => 'en'], absolute: false));
    }

    public function test_admin_with_two_factor_can_complete_password_login_and_challenge(): void
    {
        $this->seed(RoleSeeder::class);
        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();
        $user = User::factory()->create([
            'email' => 'admin-2fa@example.test',
        ]);
        $user->assignRole('admin');
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->post('/login', [
            'email' => 'admin-2fa@example.test',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('admin.two-factor.challenge', ['lang' => 'en'], absolute: false));

        $code = $google2fa->getCurrentOtp($secret);
        $this->post(route('admin.two-factor.challenge.store'), ['code' => $code])
            ->assertRedirect(AuthRedirect::homeForUser($user->fresh()));

        $this->assertAuthenticatedAs($user);
        $this->get(route('admin.cms-pages.index'))->assertOk();
    }

    public function test_admin_with_two_factor_gets_error_when_challenge_code_invalid(): void
    {
        $this->seed(RoleSeeder::class);
        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();
        $user = User::factory()->create([
            'email' => 'admin-2fa-bad@example.test',
        ]);
        $user->assignRole('admin');
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->post('/login', [
            'email' => 'admin-2fa-bad@example.test',
            'password' => 'password',
        ]);

        $this->post(route('admin.two-factor.challenge.store'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_admin_can_confirm_setup_with_valid_code(): void
    {
        $this->seed(RoleSeeder::class);
        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();
        $user = User::factory()->create();
        $user->assignRole('admin');
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => null,
        ])->save();

        $code = $google2fa->getCurrentOtp($secret);

        $this->actingAs($user)
            ->post(route('admin.two-factor.setup.store'), ['code' => $code])
            ->assertRedirect(route('admin.cms-pages.index', ['lang' => 'en'], absolute: false));

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
        $this->get(route('admin.cms-pages.index'))->assertOk();
    }
}

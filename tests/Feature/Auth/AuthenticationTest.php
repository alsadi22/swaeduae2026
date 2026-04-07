<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('data-testid="login-copy-page-url"', false);
        $response->assertSee(route('site.favicon', [], true), false);
        $response->assertSee('name="theme-color" content="#047857"', false);
        $response->assertSee('data-testid="skip-to-main-content"', false);
        $response->assertSee('id="main-content"', false);
        $response->assertSee('<title>'.e(__('Log in').' — '.__('SwaedUAE')).'</title>', false);
        $response->assertSee('rel="manifest"', false);
    }

    public function test_login_form_includes_lang_in_action_when_ar_requested(): void
    {
        $html = $this->get('/login?lang=ar')->assertOk()->getContent();
        $this->assertStringContainsString('lang=ar', $html);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', ['lang' => 'en'], absolute: false));
    }

    public function test_admin_redirects_to_admin_area_after_login(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.cms-pages.index', ['lang' => 'en'], absolute: false));
    }

    public function test_admin_login_screen_renders(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee(__('Admin sign-in'), false)
            ->assertSee('data-testid="login-copy-page-url"', false)
            ->assertSee('<title>'.e(__('Admin sign-in').' — '.__('SwaedUAE')).'</title>', false);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('home', ['lang' => 'en'], absolute: false));
    }

    public function test_logout_redirect_preserves_explicit_lang_query_from_request(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout', ['lang' => 'ar']));

        $this->assertGuest();
        $response->assertRedirect(route('home', ['lang' => 'ar'], absolute: false));
    }

    public function test_login_post_is_throttled_per_ip(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 30; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }
}

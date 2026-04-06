<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_register_url_redirects_to_volunteer_signup(): void
    {
        $this->get('/register')
            ->assertRedirect(route('register.volunteer', absolute: false));
    }

    public function test_volunteer_registration_screen_can_be_rendered(): void
    {
        $this->get('/register/volunteer')->assertOk();
    }

    public function test_volunteer_registration_includes_opportunities_footer_with_locale(): void
    {
        $oppAr = route('volunteer.opportunities.index', ['lang' => 'ar'], false);

        $this->get(route('register.volunteer', ['lang' => 'ar']))
            ->assertOk()
            ->assertSee('data-testid="register-volunteer-footer-opportunities"', false)
            ->assertSee($oppAr, false);
    }

    public function test_new_users_can_register_as_volunteer_and_must_verify_email(): void
    {
        $this->seed(RoleSeeder::class);

        $response = $this->post('/register/volunteer', [
            'first_name' => 'Test',
            'last_name' => 'Volunteer',
            'email' => 'test@example.com',
            'phone' => '+971500000000',
            'password' => 'password',
            'password_confirmation' => 'password',
            'locale_preferred' => 'en',
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice', ['lang' => 'en'], absolute: false));

        $user = User::query()->where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('volunteer'));
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertSame('Test', $user->first_name);
        $this->assertSame('Volunteer', $user->last_name);
        $this->assertSame('Test Volunteer', $user->name);
    }

    public function test_volunteer_registration_redirect_includes_chosen_ar_locale(): void
    {
        $this->seed(RoleSeeder::class);

        $response = $this->post('/register/volunteer', [
            'first_name' => 'Test',
            'last_name' => 'Arabic',
            'email' => 'ar-vol@example.com',
            'phone' => '+971500000001',
            'password' => 'password',
            'password_confirmation' => 'password',
            'locale_preferred' => 'ar',
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice', ['lang' => 'ar'], absolute: false));
    }

    public function test_volunteer_registration_is_throttled_per_ip(): void
    {
        Notification::fake();
        $this->seed(RoleSeeder::class);

        for ($i = 0; $i < 10; $i++) {
            $this->post('/register/volunteer', [
                'first_name' => 'Throttle',
                'last_name' => 'User'.$i,
                'email' => "vol-throttle-{$i}@example.org",
                'phone' => '+971500000000',
                'password' => 'password',
                'password_confirmation' => 'password',
                'locale_preferred' => 'en',
                'terms' => '1',
            ])->assertRedirect(route('verification.notice', ['lang' => 'en'], absolute: false));
            Auth::logout();
            $this->flushSession();
        }

        $this->post('/register/volunteer', [
            'first_name' => 'Blocked',
            'last_name' => 'User',
            'email' => 'vol-throttle-10@example.org',
            'phone' => '+971500000000',
            'password' => 'password',
            'password_confirmation' => 'password',
            'locale_preferred' => 'en',
            'terms' => '1',
        ])->assertStatus(429);
    }
}

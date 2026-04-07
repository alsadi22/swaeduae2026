<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.google.client_id' => 'test-google-client-id',
            'services.google.client_secret' => 'test-google-client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);
    }

    public function test_google_redirect_returns_socialite_authorize_url(): void
    {
        $this->seed(RoleSeeder::class);
        Socialite::fake('google');

        $response = $this->get('/auth/google');

        $response->assertRedirect();
        $this->assertStringContainsString('socialite.fake', $response->headers->get('Location') ?? '');
    }

    public function test_google_callback_creates_volunteer_and_logs_in(): void
    {
        $this->seed(RoleSeeder::class);

        $fake = new SocialiteUser;
        $fake->id = 'google-sub-new-1';
        $fake->name = 'Google Volunteer';
        $fake->email = 'gvolunteer@example.com';

        Socialite::fake('google', $fake);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect();
        $this->assertAuthenticated();
        $user = User::query()->where('email', 'gvolunteer@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('google-sub-new-1', $user->google_id);
        $this->assertTrue($user->hasRole('volunteer'));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_google_callback_rejects_staff_email_match(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create(['email' => 'adminmatch@example.com']);
        $admin->assignRole('admin');

        $fake = new SocialiteUser;
        $fake->id = 'google-sub-staff';
        $fake->name = 'Admin Dup';
        $fake->email = 'adminmatch@example.com';

        Socialite::fake('google', $fake);

        $this->get('/auth/google/callback')
            ->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(null, $admin->fresh()->google_id);
    }
}

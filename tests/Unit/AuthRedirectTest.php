<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\AuthRedirect;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_for_user_uses_locale_preferred_over_app_locale(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['locale_preferred' => 'ar']);
        $user->assignRole('volunteer');
        app()->setLocale('en');

        $url = AuthRedirect::homeForUser($user);

        $this->assertStringContainsString('lang=ar', $url);
        $this->assertStringContainsString('/dashboard', $url);
    }

    public function test_home_for_user_falls_back_to_app_locale_without_preference(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['locale_preferred' => null]);
        $user->assignRole('volunteer');
        app()->setLocale('ar');

        $url = AuthRedirect::homeForUser($user);

        $this->assertStringContainsString('lang=ar', $url);
    }

    public function test_home_for_user_may_append_verified_flag(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $url = AuthRedirect::homeForUser($user, true);

        $this->assertStringContainsString('verified=1', $url);
        $this->assertStringContainsString('lang=', $url);
    }
}

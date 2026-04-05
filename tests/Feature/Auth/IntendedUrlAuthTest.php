<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntendedUrlAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_safe_return_redirects_after_authentication(): void
    {
        $user = User::factory()->create();

        $this->get('/login?return='.rawurlencode('/volunteer/opportunities'));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(url('/volunteer/opportunities'));
    }

    public function test_login_with_external_return_ignored(): void
    {
        $user = User::factory()->create();

        $this->get('/login?return='.rawurlencode('https://malicious.example/hijack'));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_admin_login_accepts_return_query_and_shows_portal(): void
    {
        $this->get('/admin/login?return='.rawurlencode('/volunteer'))
            ->assertOk()
            ->assertSee(__('Admin sign-in'), false);
    }
}

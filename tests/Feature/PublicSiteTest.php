<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_ok(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_volunteer_page_is_ok(): void
    {
        $this->get('/volunteer')->assertOk();
    }

    public function test_volunteer_opportunities_list_is_ok(): void
    {
        $this->get('/volunteer/opportunities')->assertOk();
    }

    public function test_public_header_shows_opportunities_for_volunteer(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)->get('/')->assertOk()->assertSee(__('Opportunities'), false);
    }

    public function test_volunteerplatform_redirects_to_volunteer(): void
    {
        $this->get('/volunteerplatform')
            ->assertRedirect('/volunteer');
    }

    public function test_legal_pages_show_placeholder_notice(): void
    {
        foreach (['/legal/terms', '/legal/privacy', '/legal/cookies'] as $path) {
            $this->get($path)->assertOk()->assertSee('data-testid="legal-placeholder-notice"', false);
        }
    }

    public function test_public_ia_pages_are_ok(): void
    {
        foreach ([
            '/about',
            '/leadership',
            '/programs',
            '/events',
            '/media',
            '/gallery',
            '/partners',
            '/faq',
            '/support',
            '/youth-councils',
            '/legal/terms',
            '/legal/privacy',
            '/legal/cookies',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }
}

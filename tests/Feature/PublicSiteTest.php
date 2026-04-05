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

    public function test_site_webmanifest_is_served_as_json(): void
    {
        $this->get('/site.webmanifest')
            ->assertOk()
            ->assertHeader('content-type', 'application/manifest+json; charset=UTF-8')
            ->assertJsonFragment(['name' => 'SwaedUAE', 'display' => 'browser']);
    }

    public function test_home_includes_organization_json_ld(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"NGO"', false);
        $response->assertSee('"@type":"WebSite"', false);
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

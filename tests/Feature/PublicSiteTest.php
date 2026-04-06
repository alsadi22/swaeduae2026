<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PublicLocale;
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

    public function test_volunteer_hub_includes_programs_media_events_links(): void
    {
        $this->get('/volunteer')
            ->assertOk()
            ->assertSee('data-testid="volunteer-hub-programs"', false)
            ->assertSee('data-testid="volunteer-hub-media"', false)
            ->assertSee('data-testid="volunteer-hub-events"', false);
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

    public function test_about_with_lang_query_preserves_lang_in_atom_feed_link(): void
    {
        $feedUrl = route('feed', ['lang' => 'ar'], false);

        $this->get('/about?lang=ar')
            ->assertOk()
            ->assertSee($feedUrl, false);
    }

    public function test_logged_in_user_sees_preferred_lang_in_about_feed_link_when_no_query(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['locale_preferred' => 'ar']);
        $user->assignRole('volunteer');

        $feedUrl = route('feed', PublicLocale::queryForUser($user), false);

        $this->actingAs($user)
            ->get('/about')
            ->assertOk()
            ->assertSee($feedUrl, false);
    }

    public function test_gallery_page_includes_opportunities_footer_link(): void
    {
        $this->get('/gallery')
            ->assertOk()
            ->assertSee('data-testid="gallery-footer-opportunities"', false)
            ->assertSee(route('volunteer.opportunities.index', PublicLocale::query(), true), false);
    }

    public function test_faq_page_includes_opportunities_footer_link(): void
    {
        $this->get('/faq')
            ->assertOk()
            ->assertSee('data-testid="faq-footer-opportunities"', false)
            ->assertSee(route('volunteer.opportunities.index', PublicLocale::query(), true), false);
    }

    public function test_about_page_leadership_link_preserves_lang_query(): void
    {
        $leadershipUrl = route('leadership', ['lang' => 'ar'], false);

        $this->get('/about?lang=ar')
            ->assertOk()
            ->assertSee($leadershipUrl, false)
            ->assertSee('data-testid="about-footer-opportunities"', false);
    }

    public function test_leadership_page_includes_opportunities_footer_link(): void
    {
        $this->get('/leadership')
            ->assertOk()
            ->assertSee('data-testid="leadership-footer-opportunities"', false)
            ->assertSee(route('volunteer.opportunities.index', PublicLocale::query(), true), false);
    }

    public function test_legal_pages_include_opportunities_footer_with_locale(): void
    {
        $oppAr = route('volunteer.opportunities.index', ['lang' => 'ar'], false);

        $this->get('/legal/terms?lang=ar')
            ->assertOk()
            ->assertSee('data-testid="legal-terms-footer-opportunities"', false)
            ->assertSee($oppAr, false);

        $this->get('/legal/privacy?lang=ar')
            ->assertOk()
            ->assertSee('data-testid="legal-privacy-footer-opportunities"', false)
            ->assertSee($oppAr, false);

        $this->get('/legal/cookies?lang=ar')
            ->assertOk()
            ->assertSee('data-testid="legal-cookies-footer-opportunities"', false)
            ->assertSee($oppAr, false);
    }
}

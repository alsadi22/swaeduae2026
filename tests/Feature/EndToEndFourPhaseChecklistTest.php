<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Locks the four-phase “full website” information architecture and baseline security headers.
 * Traceability: Documents/END-TO-END-FOUR-PHASE-CHECKLIST.md
 */
class EndToEndFourPhaseChecklistTest extends TestCase
{
    use RefreshDatabase;

    /** @return list<array{0: string, 1: string}> */
    private function phase1PublicRoutes(): array
    {
        return [
            ['GET', '/'],
            ['GET', '/about'],
            ['GET', '/programs'],
            ['GET', '/events'],
            ['GET', '/media'],
            ['GET', '/gallery'],
            ['GET', '/partners'],
            ['GET', '/contact'],
            ['GET', '/support'],
            ['GET', '/legal/terms'],
            ['GET', '/legal/privacy'],
            ['GET', '/legal/cookies'],
            ['GET', '/leadership'],
            ['GET', '/faq'],
            ['GET', '/youth-councils'],
            ['GET', '/volunteer'],
            ['GET', '/robots.txt'],
            ['GET', '/humans.txt'],
            ['GET', '/.well-known/security.txt'],
            ['GET', '/feeds/volunteer-opportunities.atom'],
            ['GET', '/feed.xml'],
        ];
    }

    public function test_phase_1_core_public_routes_return_ok(): void
    {
        foreach ($this->phase1PublicRoutes() as [$method, $uri]) {
            $response = $this->call($method, $uri);
            if ($uri === '/support' || $uri === '/leadership') {
                $response->assertRedirect();
            } else {
                $response->assertOk();
            }
        }
    }

    public function test_phase_1_sitemap_xml_is_reachable(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function test_phase_1_public_event_detail_is_reachable(): void
    {
        $event = Event::factory()->create([
            'title_en' => 'Four-phase public event detail',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);

        $this->get(route('events.show', $event))->assertOk()->assertSee('Four-phase public event detail', false);
    }

    public function test_phase_1_web_manifest_and_favicon_are_reachable(): void
    {
        $this->get('/site.webmanifest')->assertOk();
        $this->get('/favicon.svg')->assertOk();
    }

    public function test_phase_1_public_response_includes_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_phase_2_auth_surfaces_are_reachable_for_guests(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register/volunteer')->assertOk();
        $this->get('/register/organization')->assertOk();
        $this->get('/forgot-password')->assertOk();
    }

    public function test_phase_2_profile_requires_authentication(): void
    {
        $this->get('/profile')->assertRedirect();
    }

    public function test_phase_2_volunteer_profile_requires_authentication(): void
    {
        $this->get('/volunteer/profile')->assertRedirect();
    }

    public function test_phase_2_data_export_requires_authentication(): void
    {
        $this->get('/profile/data-export')->assertRedirect(route('login', ['lang' => 'en'], absolute: false));
    }

    public function test_phase_1_not_found_page_includes_recovery_links(): void
    {
        $this->get('/no-such-page-swaeduae-e2e-xyz')
            ->assertNotFound()
            ->assertSee('data-testid="error-404-opportunities"', false)
            ->assertSee(__('Browse opportunities'), false);
    }

    public function test_phase_3_admin_login_reachable_volunteer_opportunities_public(): void
    {
        $this->get('/admin/login')->assertOk();
        $this->get('/volunteer/opportunities')->assertOk();
    }

    public function test_phase_3_organization_dashboard_requires_authentication(): void
    {
        $this->get('/organization/dashboard')->assertRedirect();
    }

    public function test_phase_3_organization_dashboard_ok_for_approved_org_manager(): void
    {
        $this->seed(RoleSeeder::class);
        $org = Organization::factory()->create();
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $org->id])->save();
        $user->assignRole('org-manager');

        $this->actingAs($user)->get(route('organization.dashboard'))->assertOk();
    }

    public function test_phase_3_organization_events_index_ok_for_approved_org_manager(): void
    {
        $this->seed(RoleSeeder::class);
        $org = Organization::factory()->create();
        $user = User::factory()->create();
        $user->forceFill(['organization_id' => $org->id])->save();
        $user->assignRole('org-manager');

        $this->actingAs($user)->get(route('organization.events.index'))->assertOk();
    }

    public function test_phase_3_guest_redirected_from_admin_cms_index(): void
    {
        $this->get('/admin/cms-pages')->assertRedirect(route('admin.login', absolute: false));
    }

    public function test_phase_4_artisan_schedule_list_succeeds(): void
    {
        $this->artisan('schedule:list')->assertSuccessful();
    }
}

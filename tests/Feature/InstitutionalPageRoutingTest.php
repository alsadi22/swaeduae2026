<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitutionalPageRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_institutional_route_uses_blade_fallback_when_no_cms_page(): void
    {
        $this->get('/about')->assertOk()->assertViewIs('public.about');
    }

    public function test_leadership_route_uses_blade_fallback_when_no_cms_page(): void
    {
        $this->get('/leadership')->assertOk()->assertViewIs('public.leadership');
    }

    public function test_cookies_route_uses_blade_fallback_when_no_cms_page(): void
    {
        $this->get('/legal/cookies')->assertOk()->assertViewIs('public.legal.cookies');
    }

    public function test_institutional_route_uses_cms_when_published_row_exists(): void
    {
        $user = User::factory()->create();
        CmsPage::query()->create([
            'slug' => 'about',
            'locale' => 'en',
            'title' => 'CMS About Title',
            'body' => 'Unique CMS body marker **bold**.',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'author_id' => $user->id,
        ]);

        $response = $this->get('/about');

        $response->assertOk();
        $response->assertViewIs('public.cms-page');
        $response->assertViewHas('cmsPage', fn (CmsPage $p) => $p->slug === 'about' && $p->title === 'CMS About Title');
        $response->assertSee('CMS About Title', false);
    }

    public function test_institutional_route_falls_back_when_cms_row_is_draft(): void
    {
        $user = User::factory()->create();
        CmsPage::query()->create([
            'slug' => 'faq',
            'locale' => 'en',
            'title' => 'Draft FAQ',
            'body' => 'Hidden',
            'status' => CmsPage::STATUS_DRAFT,
            'published_at' => null,
            'author_id' => $user->id,
        ]);

        $this->get('/faq')->assertOk()->assertViewIs('public.faq');
    }

    public function test_cms_page_public_url_maps_institutional_slugs(): void
    {
        $page = new CmsPage(['slug' => 'programs', 'locale' => 'en']);

        $this->assertStringContainsString('/programs', $page->publicUrl());
    }

    public function test_cms_page_public_url_uses_page_route_for_arbitrary_slug(): void
    {
        $page = new CmsPage(['slug' => 'community-charter', 'locale' => 'en']);

        $this->assertStringContainsString('/page/community-charter', $page->publicUrl());
    }

    public function test_cms_page_public_url_maps_cookies_slug(): void
    {
        $page = new CmsPage(['slug' => 'cookies', 'locale' => 'en']);

        $this->assertStringContainsString('/legal/cookies', $page->publicUrl());
    }

    public function test_cms_page_public_url_maps_leadership_slug(): void
    {
        $page = new CmsPage(['slug' => 'leadership', 'locale' => 'en']);

        $this->assertStringContainsString('/leadership', $page->publicUrl());
    }
}

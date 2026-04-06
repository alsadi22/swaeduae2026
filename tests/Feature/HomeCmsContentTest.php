<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeCmsContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_without_cms_rows(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_home_shows_featured_pages_marked_show_on_home(): void
    {
        $user = User::factory()->create();
        CmsPage::query()->create([
            'slug' => 'spotlight',
            'locale' => 'en',
            'title' => 'Spotlight story',
            'excerpt' => 'Short teaser for home.',
            'body' => 'Full markdown body.',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'author_id' => $user->id,
            'show_on_home' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Spotlight story', false);
        $response->assertSee('Short teaser for home.', false);
    }

    public function test_home_latest_section_lists_non_institutional_published_pages(): void
    {
        $user = User::factory()->create();
        CmsPage::query()->create([
            'slug' => 'annual-report-teaser',
            'locale' => 'en',
            'title' => 'Annual report',
            'excerpt' => 'Summary available online.',
            'body' => 'Details…',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'author_id' => $user->id,
            'show_on_home' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Annual report', false);
    }

    public function test_home_footer_includes_humans_and_security_links(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-testid="footer-humans-txt"', false)
            ->assertSee('data-testid="footer-security-txt"', false)
            ->assertSee(route('site.humans', [], true), false)
            ->assertSee(route('site.security', [], true), false);
    }
}

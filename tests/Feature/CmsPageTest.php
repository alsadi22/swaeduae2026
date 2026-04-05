<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_youth_councils_slug_redirects_to_institutional_route(): void
    {
        $this->get('/page/youth-councils')->assertRedirect(route('youth-councils', absolute: false));
    }

    public function test_published_cms_page_is_visible(): void
    {
        CmsPage::query()->create([
            'slug' => 'test-charter',
            'locale' => 'en',
            'title' => 'Test charter',
            'body' => 'Hello **world**.',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ]);

        $response = $this->get('/page/test-charter');

        $response->assertOk();
        $response->assertSee('Test charter', false);
        $response->assertSee('Hello', false);
    }

    public function test_draft_cms_page_returns_404(): void
    {
        CmsPage::query()->create([
            'slug' => 'secret',
            'locale' => 'en',
            'title' => 'Secret',
            'body' => 'Hidden',
            'status' => CmsPage::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $this->get('/page/secret')->assertNotFound();
    }

    public function test_respects_locale_for_same_slug(): void
    {
        CmsPage::query()->create([
            'slug' => 'bilingual',
            'locale' => 'en',
            'title' => 'English title',
            'body' => 'EN',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subMinute(),
        ]);
        CmsPage::query()->create([
            'slug' => 'bilingual',
            'locale' => 'ar',
            'title' => 'عنوان عربي',
            'body' => 'AR',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subMinute(),
        ]);

        $this->get('/page/bilingual?lang=en')->assertOk()->assertSee('English title', false);
        $this->get('/page/bilingual?lang=ar')->assertOk()->assertSee('عنوان عربي', false);
    }

    public function test_published_cms_page_uses_public_canonical_url_with_lang(): void
    {
        $page = CmsPage::query()->create([
            'slug' => 'canonical-test',
            'locale' => 'en',
            'title' => 'Canonical test',
            'body' => 'Body.',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subMinute(),
        ]);

        $expected = $page->absolutePublicUrl('en');

        $this->get('/page/canonical-test?lang=en')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.e($expected).'">', false)
            ->assertSee('<meta property="og:url" content="'.e($expected).'">', false);
    }

    public function test_published_cms_page_emits_og_image_when_set(): void
    {
        CmsPage::query()->create([
            'slug' => 'og-img-test',
            'locale' => 'en',
            'title' => 'OG image test',
            'body' => 'Body.',
            'og_image' => 'https://example.org/share.png',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subMinute(),
        ]);

        $this->get('/page/og-img-test?lang=en')
            ->assertOk()
            ->assertSee('<meta property="og:image" content="https://example.org/share.png">', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false);
    }

    public function test_published_cms_page_resolves_relative_og_image(): void
    {
        CmsPage::query()->create([
            'slug' => 'og-relative',
            'locale' => 'en',
            'title' => 'Relative OG',
            'body' => 'Body.',
            'og_image' => '/images/preview.jpg',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subMinute(),
        ]);

        $this->get('/page/og-relative?lang=en')
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.e(url('/images/preview.jpg')).'">', false);
    }

    public function test_published_cms_page_uses_default_og_when_row_empty(): void
    {
        config(['swaeduae.default_og_image_url' => 'https://cdn.example/default.png']);

        CmsPage::query()->create([
            'slug' => 'og-default-fallback',
            'locale' => 'en',
            'title' => 'Default OG fallback',
            'body' => 'Body.',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subMinute(),
        ]);

        $this->get('/page/og-default-fallback?lang=en')
            ->assertOk()
            ->assertSee('<meta property="og:image" content="https://cdn.example/default.png">', false);
    }
}

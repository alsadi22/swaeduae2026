<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\ExternalNewsItem;
use App\Models\ExternalNewsSource;
use App\Models\User;
use App\Support\PublicLocale;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaHubPageTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_media_hub_lists_internal_page_when_show_on_media_enabled(): void
    {
        CmsPage::query()->create([
            'slug' => 'press-release-alpha',
            'locale' => 'en',
            'title' => 'Press release alpha',
            'meta_description' => null,
            'og_image' => null,
            'excerpt' => 'Short.',
            'body' => '## Body',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => null,
            'show_on_home' => false,
            'show_on_programs' => false,
            'show_on_media' => true,
        ]);

        $this->get('/media?filter=internal')
            ->assertOk()
            ->assertSeeText('Press release alpha')
            ->assertSeeText(__('News feed'))
            ->assertSeeText(__('site.media_hub_feed_hint'))
            ->assertSee('data-testid="media-footer-opportunities"', false)
            ->assertSee(route('volunteer.opportunities.index', PublicLocale::query(), true), false);
    }

    public function test_media_hub_hides_internal_page_when_show_on_media_disabled(): void
    {
        CmsPage::query()->create([
            'slug' => 'internal-only-page',
            'locale' => 'en',
            'title' => 'Hidden from media hub',
            'meta_description' => null,
            'og_image' => null,
            'excerpt' => null,
            'body' => 'x',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => null,
            'show_on_home' => false,
            'show_on_programs' => false,
            'show_on_media' => false,
        ]);

        $this->get('/media?filter=internal')
            ->assertOk()
            ->assertDontSee('Hidden from media hub', false);
    }

    public function test_home_latest_news_teaser_respects_show_on_media(): void
    {
        CmsPage::query()->create([
            'slug' => 'home-visible-story',
            'locale' => 'en',
            'title' => 'Home visible story',
            'meta_description' => null,
            'og_image' => null,
            'excerpt' => 'Excerpt visible.',
            'body' => '## Body',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => null,
            'show_on_home' => false,
            'show_on_programs' => false,
            'show_on_media' => true,
        ]);

        CmsPage::query()->create([
            'slug' => 'home-hidden-story',
            'locale' => 'en',
            'title' => 'Home hidden story',
            'meta_description' => null,
            'og_image' => null,
            'excerpt' => 'Should not teaser.',
            'body' => '## Body',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'author_id' => null,
            'show_on_home' => false,
            'show_on_programs' => false,
            'show_on_media' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeText('Home visible story')
            ->assertDontSee('Home hidden story', false);
    }

    public function test_admin_can_disable_show_on_media_on_cms_page(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.cms-pages.store'), [
                'slug' => 'media-off-demo',
                'locale' => 'en',
                'title' => 'Media off demo',
                'meta_description' => null,
                'og_image' => null,
                'excerpt' => 'Short.',
                'body' => '## Hello',
                'status' => CmsPage::STATUS_DRAFT,
                'published_at' => null,
                'show_on_home' => '0',
                'show_on_programs' => '0',
                'show_on_media' => '0',
                'allow_partial_locale_publish' => '1',
            ])
            ->assertRedirect(route('admin.cms-pages.index', PublicLocale::query()));

        $row = CmsPage::query()->where('slug', 'media-off-demo')->where('locale', 'en')->first();
        $this->assertNotNull($row);
        $this->assertFalse($row->show_on_media);
    }

    public function test_media_hub_search_filters_internal_cms(): void
    {
        CmsPage::query()->create([
            'slug' => 'match-zebra-story',
            'locale' => 'en',
            'title' => 'Community update',
            'meta_description' => null,
            'og_image' => null,
            'excerpt' => 'Zebra keyword in excerpt.',
            'body' => '## More',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => null,
            'show_on_home' => false,
            'show_on_programs' => false,
            'show_on_media' => true,
        ]);

        CmsPage::query()->create([
            'slug' => 'other-no-match',
            'locale' => 'en',
            'title' => 'Other news',
            'meta_description' => null,
            'og_image' => null,
            'excerpt' => 'Nothing here.',
            'body' => '## Body',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => null,
            'show_on_home' => false,
            'show_on_programs' => false,
            'show_on_media' => true,
        ]);

        $this->get('/media?q='.rawurlencode('zebra'))
            ->assertOk()
            ->assertSeeText('Community update')
            ->assertDontSee('Other news', false);
    }

    public function test_media_hub_search_filters_external_items(): void
    {
        $source = ExternalNewsSource::query()->create([
            'name' => 'Test wire',
            'slug' => 'test-wire',
            'type' => ExternalNewsSource::TYPE_MANUAL,
            'endpoint_url' => 'https://example.org/feed',
            'website_url' => 'https://example.org',
            'label_en' => 'Wire EN',
            'label_ar' => 'Wire AR',
            'is_active' => true,
            'fetch_interval_minutes' => 60,
            'priority' => 0,
        ]);

        ExternalNewsItem::query()->create([
            'source_id' => $source->id,
            'external_guid' => 'g-sigma-1',
            'external_url' => 'https://example.org/a',
            'original_title' => 'Sigma wire item',
            'original_summary' => 'Brief.',
            'status' => ExternalNewsItem::STATUS_PUBLISHED,
            'import_hash' => hash('sha256', 'sigma-item-1'),
            'fetched_at' => now(),
            'published_at' => now(),
            'show_in_media_center' => true,
            'show_on_home' => false,
            'normalized_title_en' => 'Sigma wire item',
        ]);

        ExternalNewsItem::query()->create([
            'source_id' => $source->id,
            'external_guid' => 'g-other-2',
            'external_url' => 'https://example.org/b',
            'original_title' => 'Other wire',
            'original_summary' => 'Nope.',
            'status' => ExternalNewsItem::STATUS_PUBLISHED,
            'import_hash' => hash('sha256', 'other-item-2'),
            'fetched_at' => now(),
            'published_at' => now(),
            'show_in_media_center' => true,
            'show_on_home' => false,
            'normalized_title_en' => 'Other wire',
        ]);

        $this->get('/media?q=sigma&filter=external')
            ->assertOk()
            ->assertSeeText('Sigma wire item')
            ->assertDontSee('Other wire', false);
    }

    public function test_media_hub_rejects_oversized_search_query(): void
    {
        $this->get('/media?q='.str_repeat('x', 121))
            ->assertSessionHasErrors('q');
    }

    public function test_external_news_show_includes_opportunities_footer_link(): void
    {
        $source = ExternalNewsSource::query()->create([
            'name' => 'Footer test wire',
            'slug' => 'footer-test-wire',
            'type' => ExternalNewsSource::TYPE_MANUAL,
            'endpoint_url' => 'https://example.org/feed',
            'website_url' => 'https://example.org',
            'label_en' => 'Wire EN',
            'label_ar' => 'Wire AR',
            'is_active' => true,
            'fetch_interval_minutes' => 60,
            'priority' => 0,
        ]);

        $item = ExternalNewsItem::query()->create([
            'source_id' => $source->id,
            'external_guid' => 'g-footer-opp-1',
            'external_url' => 'https://example.org/story',
            'original_title' => 'Footer opportunities story',
            'original_summary' => 'Summary.',
            'status' => ExternalNewsItem::STATUS_PUBLISHED,
            'import_hash' => hash('sha256', 'footer-opp-item'),
            'fetched_at' => now(),
            'published_at' => now(),
            'show_in_media_center' => true,
            'show_on_home' => false,
            'normalized_title_en' => 'Footer opportunities story',
        ]);

        $this->get(route('media.external.show', $item))
            ->assertOk()
            ->assertSee('data-testid="external-news-footer-opportunities"', false)
            ->assertSee(route('volunteer.opportunities.index', PublicLocale::query(), true), false);
    }

    public function test_media_hub_paginates_internal_list(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            CmsPage::query()->create([
                'slug' => 'paginate-media-item-'.$i,
                'locale' => 'en',
                'title' => 'Paginated story '.$i,
                'meta_description' => null,
                'og_image' => null,
                'excerpt' => null,
                'body' => '## Body',
                'status' => CmsPage::STATUS_PUBLISHED,
                'published_at' => now()->subMinutes($i),
                'author_id' => null,
                'show_on_home' => false,
                'show_on_programs' => false,
                'show_on_media' => true,
            ]);
        }

        $this->get('/media?filter=internal')
            ->assertOk()
            ->assertSeeText('Paginated story 1')
            ->assertDontSee('Paginated story 15', false);

        $this->get('/media?filter=internal&page=2')
            ->assertOk()
            ->assertSeeText('Paginated story 15');
    }

    public function test_media_atom_feed_merges_internal_and_external_entries(): void
    {
        CmsPage::query()->create([
            'slug' => 'atom-feed-cms-item',
            'locale' => 'en',
            'title' => 'Atom feed CMS item',
            'meta_description' => null,
            'og_image' => null,
            'excerpt' => 'CMS excerpt for atom.',
            'body' => '## Body',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => null,
            'show_on_home' => false,
            'show_on_programs' => false,
            'show_on_media' => true,
        ]);

        CmsPage::query()->create([
            'slug' => 'about',
            'locale' => 'en',
            'title' => 'Institutional about in feed test',
            'meta_description' => null,
            'og_image' => null,
            'excerpt' => null,
            'body' => 'x',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => null,
            'show_on_home' => false,
            'show_on_programs' => false,
            'show_on_media' => true,
        ]);

        $source = ExternalNewsSource::query()->create([
            'name' => 'Atom wire',
            'slug' => 'atom-wire',
            'type' => ExternalNewsSource::TYPE_MANUAL,
            'endpoint_url' => 'https://example.org/feed',
            'website_url' => 'https://example.org',
            'label_en' => 'Wire EN',
            'label_ar' => 'Wire AR',
            'is_active' => true,
            'fetch_interval_minutes' => 60,
            'priority' => 0,
        ]);

        ExternalNewsItem::query()->create([
            'source_id' => $source->id,
            'external_guid' => 'g-atom-ext-1',
            'external_url' => 'https://example.org/atom',
            'original_title' => 'Atom feed external item',
            'original_summary' => 'External summary.',
            'status' => ExternalNewsItem::STATUS_PUBLISHED,
            'import_hash' => hash('sha256', 'atom-ext-1'),
            'fetched_at' => now(),
            'published_at' => now(),
            'show_in_media_center' => true,
            'show_on_home' => false,
            'normalized_title_en' => 'Atom feed external item',
        ]);

        $response = $this->get('/feed.xml');
        $response->assertOk();
        $this->assertStringContainsString('application/atom+xml', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('max-age=600', (string) $response->headers->get('Cache-Control'));
        $response->assertSee('Atom feed CMS item', false);
        $response->assertSee('Atom feed external item', false);
        $response->assertDontSee('Institutional about in feed test', false);

        $arFeed = $this->get(route('feed', ['lang' => 'ar'], false))->assertOk()->getContent();
        $this->assertStringContainsString('lang=ar', $arFeed);
    }
}

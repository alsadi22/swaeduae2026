<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\User;
use App\Support\PublicLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_page_renders(): void
    {
        $this->get('/gallery')
            ->assertOk()
            ->assertSeeText(__('Gallery'))
            ->assertSeeText(__('Photos and stories'));
    }

    public function test_gallery_lists_config_document_downloads_when_set(): void
    {
        config([
            'swaeduae.document_downloads' => [
                ['label' => 'Test annual PDF', 'label_ar' => null, 'url' => 'https://example.org/report.pdf'],
            ],
        ]);

        $this->get('/gallery')
            ->assertOk()
            ->assertSeeText('Test annual PDF')
            ->assertSeeText(__('Reports and downloads'));
    }

    public function test_gallery_lists_pages_marked_show_in_gallery(): void
    {
        CmsPage::query()->create([
            'slug' => 'photo-story-unique-xyz',
            'locale' => 'en',
            'title' => 'Community photo story unique',
            'meta_description' => null,
            'og_image' => null,
            'excerpt' => 'Short teaser for gallery card.',
            'body' => '## Body',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => null,
            'show_on_home' => false,
            'show_on_programs' => false,
            'show_on_media' => false,
            'show_in_gallery' => true,
        ]);

        $this->get('/gallery')
            ->assertOk()
            ->assertSee('Community photo story unique', false)
            ->assertSee('Short teaser for gallery card.', false)
            ->assertSee(route('cms.page', ['slug' => 'photo-story-unique-xyz', 'lang' => 'en'], true), false);
    }

    public function test_gallery_shows_cms_intro_when_gallery_slug_published(): void
    {
        $user = User::factory()->create();
        CmsPage::query()->create([
            'slug' => 'gallery',
            'locale' => 'en',
            'title' => 'Gallery CMS heading unique',
            'excerpt' => 'Intro excerpt gallery.',
            'body' => '## Intro markdown gallery',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'author_id' => $user->id,
            'show_on_home' => false,
            'show_on_programs' => false,
            'show_on_media' => false,
            'show_in_gallery' => false,
        ]);

        $this->get('/gallery?lang=en')
            ->assertOk()
            ->assertSee('Gallery CMS heading unique', false)
            ->assertSee('"@type":"Article"', false)
            ->assertSee(route('gallery', PublicLocale::query(), true), false);
    }
}

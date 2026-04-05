<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\User;
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
            ->assertSeeText('Press release alpha');
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
            ->assertRedirect(route('admin.cms-pages.index'));

        $row = CmsPage::query()->where('slug', 'media-off-demo')->where('locale', 'en')->first();
        $this->assertNotNull($row);
        $this->assertFalse($row->show_on_media);
    }
}

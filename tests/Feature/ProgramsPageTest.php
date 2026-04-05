<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramsPageTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_programs_page_renders_fallback_intro_when_no_cms_programs_row(): void
    {
        $response = $this->get('/programs');
        $response->assertOk()
            ->assertSeeText(__('Programs & initiatives'))
            ->assertSeeText(__('Featured program pages'));
        $response->assertSee(route('programs.index', ['lang' => app()->getLocale()], true), false);
    }

    public function test_programs_index_search_filters_by_title_or_body(): void
    {
        CmsPage::query()->create([
            'slug' => 'alpha-prog-search-xyz',
            'locale' => 'en',
            'title' => 'Alpha unique program title',
            'meta_description' => null,
            'og_image' => null,
            'excerpt' => null,
            'body' => '## Other body',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => null,
            'show_on_home' => false,
            'show_on_programs' => true,
        ]);
        CmsPage::query()->create([
            'slug' => 'beta-prog-other',
            'locale' => 'en',
            'title' => 'Beta unrelated',
            'meta_description' => null,
            'og_image' => null,
            'excerpt' => null,
            'body' => '## Beta body',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => null,
            'show_on_home' => false,
            'show_on_programs' => true,
        ]);

        $this->get(route('programs.index', ['q' => 'unique program']))
            ->assertOk()
            ->assertSee('Alpha unique program title', false)
            ->assertDontSee('Beta unrelated', false);
    }

    public function test_programs_index_rejects_oversized_search_query(): void
    {
        $this->get(route('programs.index', ['q' => str_repeat('x', 121)]))
            ->assertSessionHasErrors('q');
    }

    public function test_programs_page_emits_article_json_ld_when_cms_programs_intro_exists(): void
    {
        $user = User::factory()->create();
        CmsPage::query()->create([
            'slug' => 'programs',
            'locale' => 'en',
            'title' => 'Programs CMS heading',
            'excerpt' => 'Intro excerpt for programs index.',
            'body' => '## Intro body',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'author_id' => $user->id,
            'show_on_home' => false,
            'show_on_programs' => false,
            'show_on_media' => false,
        ]);

        $this->get('/programs?lang=en')
            ->assertOk()
            ->assertSee('"@type":"Article"', false)
            ->assertSee('"headline":"Programs CMS heading"', false)
            ->assertSee('property="og:type" content="article"', false);
    }

    public function test_programs_page_lists_pages_marked_show_on_programs(): void
    {
        CmsPage::query()->create([
            'slug' => 'youth-reading-club',
            'locale' => 'en',
            'title' => 'Youth reading club',
            'meta_description' => null,
            'og_image' => null,
            'excerpt' => 'Books and mentors for teens.',
            'body' => '## Program body',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => null,
            'show_on_home' => false,
            'show_on_programs' => true,
        ]);

        $this->get('/programs')
            ->assertOk()
            ->assertSee('Youth reading club', false)
            ->assertSee('Books and mentors for teens.', false);
    }

    public function test_programs_page_paginates_featured_grid(): void
    {
        for ($i = 0; $i < 13; $i++) {
            CmsPage::query()->create([
                'slug' => 'programs-paginate-'.$i,
                'locale' => 'en',
                'title' => 'Programs Paginate Title '.$i,
                'meta_description' => null,
                'og_image' => null,
                'excerpt' => 'Excerpt '.$i,
                'body' => '## Body',
                'status' => CmsPage::STATUS_PUBLISHED,
                'published_at' => now()->subDays(30 - $i),
                'author_id' => null,
                'show_on_home' => false,
                'show_on_programs' => true,
            ]);
        }

        $this->get('/programs')
            ->assertOk()
            ->assertSee('Programs Paginate Title 12', false)
            ->assertSee('Programs Paginate Title 1', false)
            ->assertDontSee('Programs Paginate Title 0', false);

        $this->get('/programs?page=2')
            ->assertOk()
            ->assertSee('Programs Paginate Title 0', false)
            ->assertDontSee('Programs Paginate Title 12', false);
    }

    public function test_programs_page_excludes_institutional_slugs_from_grid(): void
    {
        CmsPage::query()->create([
            'slug' => 'about',
            'locale' => 'en',
            'title' => 'About dup',
            'meta_description' => null,
            'og_image' => null,
            'excerpt' => null,
            'body' => 'x',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => null,
            'show_on_home' => false,
            'show_on_programs' => true,
        ]);

        $this->get('/programs')
            ->assertOk()
            ->assertDontSee('About dup', false);
    }

    public function test_admin_can_set_show_on_programs_on_cms_page(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.cms-pages.store'), [
                'slug' => 'pilot-initiative',
                'locale' => 'en',
                'title' => 'Pilot initiative',
                'meta_description' => null,
                'og_image' => null,
                'excerpt' => 'Short.',
                'body' => '## Hello',
                'status' => CmsPage::STATUS_DRAFT,
                'published_at' => null,
                'show_on_home' => '0',
                'show_on_programs' => '1',
                'allow_partial_locale_publish' => '1',
            ])
            ->assertRedirect(route('admin.cms-pages.index'));

        $row = CmsPage::query()->where('slug', 'pilot-initiative')->where('locale', 'en')->first();
        $this->assertNotNull($row);
        $this->assertTrue($row->show_on_programs);
    }
}

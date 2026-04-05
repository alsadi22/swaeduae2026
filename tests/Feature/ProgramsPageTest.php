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
        $this->get('/programs')
            ->assertOk()
            ->assertSeeText(__('Programs & initiatives'))
            ->assertSeeText(__('Featured program pages'));
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

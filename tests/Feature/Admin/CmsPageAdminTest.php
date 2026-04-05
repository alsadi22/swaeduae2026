<?php

namespace Tests\Feature\Admin;

use App\Models\CmsPage;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPageAdminTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoles(): void
    {
        $this->seed(RoleSeeder::class);
    }

    private function adminUser(): User
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_guest_redirected_from_admin_cms_index(): void
    {
        $this->get('/admin/cms-pages')->assertRedirect(route('admin.login'));
    }

    public function test_volunteer_cannot_access_admin_cms(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)->get('/admin/cms-pages')->assertForbidden();
    }

    public function test_admin_can_view_cms_index(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)->get('/admin/cms-pages')->assertOk();
    }

    public function test_admin_cms_index_shows_visibility_badges_when_flags_set(): void
    {
        $user = $this->adminUser();
        CmsPage::query()->create([
            'slug' => 'visibility-flags-row',
            'locale' => 'en',
            'title' => 'Visibility Flags Row Title',
            'body' => 'x',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $user->id,
            'show_on_home' => true,
            'show_on_programs' => true,
            'show_on_media' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.cms-pages.index'))
            ->assertOk()
            ->assertSee('title="'.__('Show on home page').'"', false)
            ->assertSee('title="'.__('Show on programs page').'"', false)
            ->assertSee('title="'.__('Show in media center').'"', false);
    }

    public function test_admin_cms_index_search_filters_by_title_or_slug(): void
    {
        $user = $this->adminUser();
        CmsPage::query()->create([
            'slug' => 'alpha-cms-filter-slug',
            'locale' => 'en',
            'title' => 'Other CMS Row Title',
            'body' => 'x',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $user->id,
        ]);
        CmsPage::query()->create([
            'slug' => 'beta-unique-cms-slug-xyz',
            'locale' => 'en',
            'title' => 'Unrelated',
            'body' => 'y',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.cms-pages.index', ['search' => 'unique-cms-slug']))
            ->assertOk()
            ->assertSee('beta-unique-cms-slug-xyz', false)
            ->assertDontSee('Other CMS Row Title', false);
    }

    public function test_admin_cms_index_search_finds_slug_with_underscores(): void
    {
        $user = $this->adminUser();
        CmsPage::query()->create([
            'slug' => 'faq_items_demo_slug',
            'locale' => 'en',
            'title' => 'Other Title X',
            'body' => 'x',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $user->id,
        ]);
        CmsPage::query()->create([
            'slug' => 'unrelated-page',
            'locale' => 'en',
            'title' => 'Unrelated',
            'body' => 'y',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.cms-pages.index', ['search' => 'faq_items']))
            ->assertOk()
            ->assertSee('faq_items_demo_slug', false)
            ->assertDontSee('unrelated-page', false);
    }

    public function test_admin_cms_index_search_max_length_validation(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.cms-pages.index', ['search' => str_repeat('s', 101)]))
            ->assertSessionHasErrors('search');
    }

    public function test_super_admin_can_view_cms_index(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)->get('/admin/cms-pages')->assertOk();
    }

    public function test_admin_can_create_cms_page(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post('/admin/cms-pages', [
            'slug' => 'admin-test-page',
            'locale' => 'en',
            'title' => 'Admin test',
            'meta_description' => null,
            'og_image' => 'https://example.com/card.jpg',
            'excerpt' => null,
            'body' => '# Hello',
            'status' => CmsPage::STATUS_DRAFT,
            'published_at' => null,
            'show_on_home' => '0',
        ]);

        $response->assertRedirect(route('admin.cms-pages.index'));
        $this->assertDatabaseHas('cms_pages', [
            'slug' => 'admin-test-page',
            'author_id' => $user->id,
            'og_image' => 'https://example.com/card.jpg',
        ]);
    }

    public function test_admin_cannot_save_invalid_og_image(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)->post('/admin/cms-pages', [
            'slug' => 'bad-og',
            'locale' => 'en',
            'title' => 'Bad OG',
            'meta_description' => null,
            'og_image' => 'not-a-valid-value',
            'excerpt' => null,
            'body' => '# x',
            'status' => CmsPage::STATUS_DRAFT,
            'published_at' => null,
            'show_on_home' => '0',
        ])->assertSessionHasErrors('og_image');

        $this->assertDatabaseMissing('cms_pages', ['slug' => 'bad-og']);
    }

    public function test_admin_can_update_cms_page(): void
    {
        $user = $this->adminUser();
        $page = CmsPage::query()->create([
            'slug' => 'editable',
            'locale' => 'en',
            'title' => 'Before',
            'body' => 'x',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put("/admin/cms-pages/{$page->id}", [
            'slug' => 'editable',
            'locale' => 'en',
            'title' => 'After',
            'meta_description' => null,
            'og_image' => null,
            'excerpt' => null,
            'body' => 'y',
            'status' => CmsPage::STATUS_DRAFT,
            'published_at' => null,
            'show_on_home' => '0',
        ]);

        $response->assertRedirect(route('admin.cms-pages.index'));
        $this->assertDatabaseHas('cms_pages', [
            'id' => $page->id,
            'title' => 'After',
        ]);
    }

    public function test_admin_can_delete_cms_page(): void
    {
        $user = $this->adminUser();
        $page = CmsPage::query()->create([
            'slug' => 'to-delete',
            'locale' => 'en',
            'title' => 'Gone',
            'body' => 'x',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete("/admin/cms-pages/{$page->id}");

        $response->assertRedirect(route('admin.cms-pages.index'));
        $this->assertDatabaseMissing('cms_pages', ['id' => $page->id]);
    }

    public function test_admin_can_preview_draft_cms_page(): void
    {
        $user = $this->adminUser();
        $page = CmsPage::query()->create([
            'slug' => 'draft-only',
            'locale' => 'en',
            'title' => 'Draft title',
            'body' => 'Draft **body**.',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $user->id,
        ]);

        $this->get('/page/draft-only')->assertNotFound();

        $response = $this->actingAs($user)->get(
            route('admin.cms-pages.preview', $page).'?lang=en'
        );

        $response->assertOk();
        $response->assertSee('Draft title', false);
        $response->assertSee(__('Preview mode'), false);
    }

    public function test_admin_cannot_publish_without_matching_locale_row(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post('/admin/cms-pages', [
            'slug' => 'bilingual-gate',
            'locale' => 'en',
            'title' => 'English only',
            'meta_description' => null,
            'excerpt' => null,
            'body' => '# EN',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->format('Y-m-d\TH:i'),
            'show_on_home' => '0',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseMissing('cms_pages', ['slug' => 'bilingual-gate']);
    }

    public function test_admin_cannot_publish_when_sibling_locale_missing_body(): void
    {
        $user = $this->adminUser();

        CmsPage::query()->create([
            'slug' => 'pair-body',
            'locale' => 'ar',
            'title' => 'عنوان',
            'body' => ' ',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post('/admin/cms-pages', [
            'slug' => 'pair-body',
            'locale' => 'en',
            'title' => 'EN title',
            'meta_description' => null,
            'excerpt' => null,
            'body' => '# EN',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->format('Y-m-d\TH:i'),
            'show_on_home' => '0',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_admin_can_publish_when_both_locales_have_content(): void
    {
        $user = $this->adminUser();

        CmsPage::query()->create([
            'slug' => 'pair-ok',
            'locale' => 'ar',
            'title' => 'عنوان',
            'body' => 'نص',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post('/admin/cms-pages', [
            'slug' => 'pair-ok',
            'locale' => 'en',
            'title' => 'EN title',
            'meta_description' => null,
            'excerpt' => null,
            'body' => '# EN',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->format('Y-m-d\TH:i'),
            'show_on_home' => '0',
        ]);

        $response->assertRedirect(route('admin.cms-pages.index'));
        $this->assertDatabaseHas('cms_pages', [
            'slug' => 'pair-ok',
            'locale' => 'en',
            'status' => CmsPage::STATUS_PUBLISHED,
        ]);
    }

    public function test_admin_can_publish_single_locale_when_partial_exception_checked(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post('/admin/cms-pages', [
            'slug' => 'en-only-exception',
            'locale' => 'en',
            'title' => 'English only',
            'meta_description' => null,
            'excerpt' => null,
            'body' => '# EN',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->format('Y-m-d\TH:i'),
            'show_on_home' => '0',
            'allow_partial_locale_publish' => '1',
        ]);

        $response->assertRedirect(route('admin.cms-pages.index'));
        $this->assertDatabaseHas('cms_pages', [
            'slug' => 'en-only-exception',
            'locale' => 'en',
            'status' => CmsPage::STATUS_PUBLISHED,
        ]);
    }

    public function test_volunteer_cannot_preview_cms_page(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $page = CmsPage::query()->create([
            'slug' => 'locked',
            'locale' => 'en',
            'title' => 'X',
            'body' => 'y',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $admin->id,
        ]);

        $volunteer = User::factory()->create();
        $volunteer->assignRole('volunteer');

        $this->actingAs($volunteer)
            ->get(route('admin.cms-pages.preview', $page))
            ->assertForbidden();
    }
}

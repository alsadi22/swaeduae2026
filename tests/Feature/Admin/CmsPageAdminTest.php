<?php

namespace Tests\Feature\Admin;

use App\Models\CmsPage;
use App\Models\User;
use App\Support\PublicLocale;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    /** Minimal valid 1×1 PNG (no GD required). */
    private function tinyPng(): UploadedFile
    {
        $binary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);

        return UploadedFile::fake()->createWithContent('og.png', $binary);
    }

    private function assertOgStoredOnPublicDisk(CmsPage $page): string
    {
        $this->assertIsString($page->og_image);
        $this->assertStringStartsWith('/storage/cms/og/'.$page->id.'/', $page->og_image);
        $relative = substr($page->og_image, strlen('/storage/'));
        Storage::disk('public')->assertExists($relative);

        return $relative;
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

        $this->actingAs($user)
            ->get('/admin/cms-pages')
            ->assertForbidden()
            ->assertSee('data-testid="error-403-copy-page-url"', false)
            ->assertSee(__('Access denied'), false);
    }

    public function test_admin_can_view_cms_index(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get('/admin/cms-pages')
            ->assertOk()
            ->assertSee('data-testid="admin-cms-pages-export-csv"', false)
            ->assertSee('data-testid="admin-cms-pages-copy-filtered-url"', false)
            ->assertSee('<title>'.e(__('CMS pages').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false);
    }

    public function test_admin_can_download_cms_pages_csv(): void
    {
        $user = $this->adminUser();
        CmsPage::query()->create([
            'slug' => 'csv-cms-slug-unique',
            'locale' => 'en',
            'title' => 'Csv Cms Title Unique',
            'body' => 'body',
            'status' => CmsPage::STATUS_PUBLISHED,
            'author_id' => $user->id,
            'show_on_home' => false,
        ]);

        $response = $this->actingAs($user)->get(route('admin.cms-pages.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('csv-cms-slug-unique', $content);
        $this->assertStringContainsString('Csv Cms Title Unique', $content);
    }

    public function test_volunteer_cannot_access_cms_pages_export(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)->get(route('admin.cms-pages.export'))->assertForbidden();
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
            ->assertSee('data-admin-shell="sidebar-v1"', false)
            ->assertSee('title="'.__('Show on home page').'"', false)
            ->assertSee('title="'.__('Show on programs page').'"', false)
            ->assertSee('title="'.__('Show in media center').'"', false);
    }

    public function test_admin_cms_index_rejects_invalid_placement(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.cms-pages.index', array_merge(PublicLocale::query(), ['placement' => 'storefront'])))
            ->assertSessionHasErrors('placement');
    }

    public function test_admin_cms_index_placement_filter_limits_rows(): void
    {
        $user = $this->adminUser();
        CmsPage::query()->create([
            'slug' => 'placement-home-only-en',
            'locale' => 'en',
            'title' => 'Home Surface Only Title',
            'body' => 'x',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $user->id,
            'show_on_home' => true,
            'show_on_programs' => false,
            'show_on_media' => false,
        ]);
        CmsPage::query()->create([
            'slug' => 'placement-not-on-home-en',
            'locale' => 'en',
            'title' => 'Not On Home Row Title',
            'body' => 'x',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $user->id,
            'show_on_home' => false,
            'show_on_programs' => true,
            'show_on_media' => false,
        ]);

        $this->actingAs($user)
            ->get(route('admin.cms-pages.index', array_merge(PublicLocale::query(), ['placement' => 'home'])))
            ->assertOk()
            ->assertSee('Home Surface Only Title', false)
            ->assertDontSee('Not On Home Row Title', false);
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

    public function test_admin_sidebar_includes_locale_switch(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.cms-pages.index', ['lang' => 'ar']))
            ->assertOk()
            ->assertSee('data-testid="admin-sidebar-locale-switch"', false)
            ->assertSee(__('Admin interface language'), false);
    }

    public function test_admin_can_upload_og_image_when_creating_cms_page(): void
    {
        Storage::fake('public');
        $user = $this->adminUser();
        $file = $this->tinyPng();

        $this->actingAs($user)->post('/admin/cms-pages', [
            'slug' => 'og-upload-create',
            'locale' => 'en',
            'title' => 'OG upload',
            'meta_description' => null,
            'og_image' => null,
            'og_image_upload' => $file,
            'excerpt' => null,
            'body' => '# Hello',
            'status' => CmsPage::STATUS_DRAFT,
            'published_at' => null,
            'show_on_home' => '0',
        ])->assertRedirect(route('admin.cms-pages.index', PublicLocale::query()));

        $page = CmsPage::query()->where('slug', 'og-upload-create')->first();
        $this->assertNotNull($page);
        $this->assertOgStoredOnPublicDisk($page);
    }

    public function test_admin_upload_on_update_replaces_previous_managed_og_image(): void
    {
        Storage::fake('public');
        $user = $this->adminUser();

        $this->actingAs($user)->post('/admin/cms-pages', [
            'slug' => 'og-replace',
            'locale' => 'en',
            'title' => 'First',
            'meta_description' => null,
            'og_image' => null,
            'og_image_upload' => $this->tinyPng(),
            'excerpt' => null,
            'body' => '# a',
            'status' => CmsPage::STATUS_DRAFT,
            'published_at' => null,
            'show_on_home' => '0',
        ]);

        $page = CmsPage::query()->where('slug', 'og-replace')->first();
        $this->assertNotNull($page);
        $firstRelative = $this->assertOgStoredOnPublicDisk($page);

        $second = UploadedFile::fake()->createWithContent('og2.png', (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true));

        $this->actingAs($user)->put("/admin/cms-pages/{$page->id}", [
            'slug' => 'og-replace',
            'locale' => 'en',
            'title' => 'Second',
            'meta_description' => null,
            'og_image' => $page->og_image,
            'og_image_upload' => $second,
            'excerpt' => null,
            'body' => '# b',
            'status' => CmsPage::STATUS_DRAFT,
            'published_at' => null,
            'show_on_home' => '0',
        ])->assertRedirect(route('admin.cms-pages.index', PublicLocale::query()));

        $page->refresh();
        $secondRelative = $this->assertOgStoredOnPublicDisk($page);
        Storage::disk('public')->assertMissing($firstRelative);
        $this->assertNotSame($firstRelative, $secondRelative);
    }

    public function test_admin_can_remove_managed_og_image_on_update(): void
    {
        Storage::fake('public');
        $user = $this->adminUser();

        $this->actingAs($user)->post('/admin/cms-pages', [
            'slug' => 'og-remove',
            'locale' => 'en',
            'title' => 'R',
            'meta_description' => null,
            'og_image' => null,
            'og_image_upload' => $this->tinyPng(),
            'excerpt' => null,
            'body' => '# x',
            'status' => CmsPage::STATUS_DRAFT,
            'published_at' => null,
            'show_on_home' => '0',
        ]);

        $page = CmsPage::query()->where('slug', 'og-remove')->first();
        $this->assertNotNull($page);
        $relative = $this->assertOgStoredOnPublicDisk($page);

        $this->actingAs($user)->put("/admin/cms-pages/{$page->id}", [
            'slug' => 'og-remove',
            'locale' => 'en',
            'title' => 'R',
            'meta_description' => null,
            'og_image' => $page->og_image,
            'excerpt' => null,
            'body' => '# x',
            'status' => CmsPage::STATUS_DRAFT,
            'published_at' => null,
            'show_on_home' => '0',
            'remove_og_image' => '1',
        ])->assertRedirect(route('admin.cms-pages.index', PublicLocale::query()));

        $page->refresh();
        $this->assertNull($page->og_image);
        Storage::disk('public')->assertMissing($relative);
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

        $response->assertRedirect(route('admin.cms-pages.index', PublicLocale::query()));
        $this->actingAs($user)
            ->get(route('admin.cms-pages.index', PublicLocale::query()))
            ->assertOk()
            ->assertSee('data-testid="admin-cms-pages-flash-status"', false)
            ->assertSee(__('CMS page created.'), false);
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

        $response->assertRedirect(route('admin.cms-pages.index', PublicLocale::query()));
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

        $response->assertRedirect(route('admin.cms-pages.index', PublicLocale::query()));
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
        $response->assertDontSee('data-testid="cms-page-copy-page-url"', false);
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

        $response->assertRedirect(route('admin.cms-pages.index', PublicLocale::query()));
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

        $response->assertRedirect(route('admin.cms-pages.index', PublicLocale::query()));
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

    public function test_admin_cms_page_edit_includes_copy_page_url_control(): void
    {
        $user = $this->adminUser();
        $page = CmsPage::query()->create([
            'slug' => 'edit-copy-url-slug',
            'locale' => 'en',
            'title' => 'Edit Copy Url Title',
            'body' => 'body',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.cms-pages.edit', array_merge(['cms_page' => $page], PublicLocale::query())))
            ->assertOk()
            ->assertSee('<title>'.e(__('Edit CMS page').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-testid="admin-cms-page-edit-copy-page-url"', false);
    }

    public function test_admin_cms_page_create_includes_copy_page_url_control(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.cms-pages.create', PublicLocale::query()))
            ->assertOk()
            ->assertSee('<title>'.e(__('New CMS page').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-testid="admin-cms-page-create-copy-page-url"', false);
    }
}

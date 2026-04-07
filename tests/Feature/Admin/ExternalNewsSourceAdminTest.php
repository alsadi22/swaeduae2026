<?php

namespace Tests\Feature\Admin;

use App\Models\ExternalNewsSource;
use App\Models\User;
use App\Support\PublicLocale;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExternalNewsSourceAdminTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_admin_external_news_sources_index_shows_export_control(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.external-news-sources.index'))
            ->assertOk()
            ->assertSee('<title>'.e(__('News sources').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-testid="admin-external-news-sources-copy-filtered-url"', false)
            ->assertSee('data-testid="admin-external-news-sources-export-csv"', false);
    }

    public function test_admin_can_download_external_news_sources_csv(): void
    {
        $user = $this->adminUser();
        ExternalNewsSource::query()->create([
            'name' => 'ExportSourceUniqueName',
            'slug' => 'export-source-unique',
            'type' => ExternalNewsSource::TYPE_RSS,
            'endpoint_url' => 'https://example.org/feed',
            'website_url' => 'https://example.org',
            'label_en' => 'Export Source',
            'label_ar' => 'مصدر',
            'is_active' => true,
            'fetch_interval_minutes' => 60,
            'priority' => 5,
        ]);

        $response = $this->actingAs($user)->get(route('admin.external-news-sources.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('ExportSourceUniqueName', $content);
        $this->assertStringContainsString('export-source-unique', $content);
    }

    public function test_volunteer_cannot_access_external_news_sources_export(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)->get(route('admin.external-news-sources.export'))->assertForbidden();
    }

    public function test_admin_external_news_source_logs_includes_copy_filtered_url_control(): void
    {
        $user = $this->adminUser();
        $source = ExternalNewsSource::query()->create([
            'name' => 'LogsPageSource',
            'slug' => 'logs-page-source',
            'type' => ExternalNewsSource::TYPE_MANUAL,
            'endpoint_url' => 'https://example.org/feed',
            'website_url' => 'https://example.org',
            'label_en' => 'Logs EN',
            'label_ar' => 'سجلات',
            'is_active' => true,
            'fetch_interval_minutes' => 60,
            'priority' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('admin.external-news-sources.logs', ['external_news_source' => $source]))
            ->assertOk()
            ->assertSee('<title>'.e(__('Fetch logs').' — '.Str::limit($source->name, 60, '…').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-testid="admin-external-news-source-logs-copy-filtered-url"', false);
    }

    public function test_admin_external_news_source_create_includes_copy_page_url_control(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.external-news-sources.create', PublicLocale::query()))
            ->assertOk()
            ->assertSee('<title>'.e(__('Add news source').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-testid="admin-external-news-source-create-copy-page-url"', false);
    }

    public function test_admin_external_news_source_edit_includes_copy_page_url_control(): void
    {
        $user = $this->adminUser();
        $source = ExternalNewsSource::query()->create([
            'name' => 'EditCopySource',
            'slug' => 'edit-copy-source',
            'type' => ExternalNewsSource::TYPE_RSS,
            'endpoint_url' => 'https://example.org/feed',
            'website_url' => 'https://example.org',
            'label_en' => 'Edit Copy',
            'label_ar' => 'تعديل',
            'is_active' => true,
            'fetch_interval_minutes' => 60,
            'priority' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('admin.external-news-sources.edit', array_merge(['external_news_source' => $source], PublicLocale::query())))
            ->assertOk()
            ->assertSee('<title>'.e(__('Edit news source').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-testid="admin-external-news-source-edit-copy-page-url"', false);
    }
}

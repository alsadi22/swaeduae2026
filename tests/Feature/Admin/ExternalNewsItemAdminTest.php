<?php

namespace Tests\Feature\Admin;

use App\Models\ExternalNewsItem;
use App\Models\ExternalNewsSource;
use App\Models\User;
use App\Support\PublicLocale;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalNewsItemAdminTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_admin_can_filter_external_news_items_by_title_search(): void
    {
        $user = $this->adminUser();
        $source = ExternalNewsSource::query()->create([
            'name' => 'Source A',
            'slug' => 'source-a',
            'type' => ExternalNewsSource::TYPE_RSS,
            'endpoint_url' => 'https://example.org/feed',
            'website_url' => 'https://example.org',
            'label_en' => 'Source A',
            'label_ar' => 'مصدر أ',
            'is_active' => true,
            'fetch_interval_minutes' => 60,
            'priority' => 0,
        ]);

        ExternalNewsItem::query()->create([
            'source_id' => $source->id,
            'external_guid' => 'g1',
            'external_url' => 'https://example.org/1',
            'original_title' => 'UniqueHeadlineAlpha',
            'original_summary' => null,
            'status' => ExternalNewsItem::STATUS_PENDING_REVIEW,
            'fetched_at' => now(),
            'import_hash' => 'h1',
        ]);
        ExternalNewsItem::query()->create([
            'source_id' => $source->id,
            'external_guid' => 'g2',
            'external_url' => 'https://example.org/2',
            'original_title' => 'OtherStoryBeta',
            'normalized_title_en' => 'Other Story',
            'original_summary' => null,
            'status' => ExternalNewsItem::STATUS_PENDING_REVIEW,
            'fetched_at' => now(),
            'import_hash' => 'h2',
        ]);

        $this->actingAs($user)
            ->get(route('admin.external-news-items.index', array_merge(PublicLocale::query(), ['search' => 'UniqueHeadline'])))
            ->assertOk()
            ->assertSee('UniqueHeadlineAlpha', false)
            ->assertDontSee('OtherStoryBeta', false);
    }

    public function test_admin_external_news_items_index_rejects_oversized_search(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.external-news-items.index', array_merge(PublicLocale::query(), ['search' => str_repeat('z', 101)])))
            ->assertSessionHasErrors('search');
    }

    public function test_admin_external_news_items_index_shows_export_and_copy_controls(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.external-news-items.index'))
            ->assertOk()
            ->assertSee('<title>'.e(__('External news').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-testid="admin-external-news-items-export-csv"', false)
            ->assertSee('data-testid="admin-external-news-items-copy-filtered-url"', false);
    }

    public function test_admin_can_download_external_news_items_csv(): void
    {
        $user = $this->adminUser();
        $source = ExternalNewsSource::query()->create([
            'name' => 'Csv Source',
            'slug' => 'csv-source',
            'type' => ExternalNewsSource::TYPE_RSS,
            'endpoint_url' => 'https://example.org/feed',
            'website_url' => 'https://example.org',
            'label_en' => 'Csv Source',
            'label_ar' => 'مصدر',
            'is_active' => true,
            'fetch_interval_minutes' => 60,
            'priority' => 0,
        ]);

        $item = ExternalNewsItem::query()->create([
            'source_id' => $source->id,
            'external_guid' => 'csv-g',
            'external_url' => 'https://example.org/article',
            'original_title' => 'CsvItemTitleUnique',
            'original_summary' => null,
            'status' => ExternalNewsItem::STATUS_PENDING_REVIEW,
            'fetched_at' => now(),
            'import_hash' => 'csv-hash',
        ]);

        $response = $this->actingAs($user)->get(route('admin.external-news-items.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString((string) $item->id, $content);
        $this->assertStringContainsString('CsvItemTitleUnique', $content);
    }

    public function test_volunteer_cannot_access_external_news_items_export(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)->get(route('admin.external-news-items.export'))->assertForbidden();
    }

    public function test_admin_external_news_item_edit_includes_copy_page_url_control(): void
    {
        $user = $this->adminUser();
        $source = ExternalNewsSource::query()->create([
            'name' => 'Edit Page Source',
            'slug' => 'edit-page-source',
            'type' => ExternalNewsSource::TYPE_RSS,
            'endpoint_url' => 'https://example.org/feed',
            'website_url' => 'https://example.org',
            'label_en' => 'Edit Page Source',
            'label_ar' => 'مصدر',
            'is_active' => true,
            'fetch_interval_minutes' => 60,
            'priority' => 0,
        ]);

        $item = ExternalNewsItem::query()->create([
            'source_id' => $source->id,
            'external_guid' => 'edit-copy-g',
            'external_url' => 'https://example.org/edit-copy',
            'original_title' => 'EditCopyItemTitleUnique',
            'original_summary' => null,
            'status' => ExternalNewsItem::STATUS_PENDING_REVIEW,
            'fetched_at' => now(),
            'import_hash' => 'edit-copy-hash',
        ]);

        $this->actingAs($user)
            ->get(route('admin.external-news-items.edit', array_merge(['external_news_item' => $item], PublicLocale::query())))
            ->assertOk()
            ->assertSee('<title>'.e(__('Review external item').' — '.__('SwaedUAE')).'</title>', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-testid="admin-external-news-item-edit-copy-page-url"', false)
            ->assertSee('EditCopyItemTitleUnique', false);
    }
}

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
}

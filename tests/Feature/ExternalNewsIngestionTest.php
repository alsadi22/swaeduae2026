<?php

namespace Tests\Feature;

use App\Models\ExternalNewsItem;
use App\Models\ExternalNewsSource;
use App\Services\ExternalNews\ExternalNewsRssIngestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalNewsIngestionTest extends TestCase
{
    use RefreshDatabase;

    private function sampleRss(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"><channel><title>T</title>
<item><title>Headline</title><link>https://gov.example/news/1</link><description><![CDATA[Summary text.]]></description><pubDate>Mon, 01 Jan 2024 12:00:00 GMT</pubDate><guid>guid-1</guid></item>
</channel></rss>';
    }

    public function test_rss_ingestion_stores_pending_review_items(): void
    {
        Http::fake([
            'https://gov.example/*' => Http::response($this->sampleRss(), 200),
        ]);

        $source = ExternalNewsSource::query()->create([
            'name' => 'Gov',
            'slug' => 'gov',
            'type' => ExternalNewsSource::TYPE_RSS,
            'endpoint_url' => 'https://gov.example/feed.xml',
            'website_url' => 'https://gov.example',
            'label_en' => 'Government',
            'label_ar' => 'حكومة',
            'is_active' => true,
            'fetch_interval_minutes' => 60,
            'priority' => 0,
        ]);

        app(ExternalNewsRssIngestionService::class)->ingestFromSource($source);

        $this->assertDatabaseHas('external_news_items', [
            'source_id' => $source->id,
            'status' => ExternalNewsItem::STATUS_PENDING_REVIEW,
            'original_title' => 'Headline',
        ]);
    }

    public function test_second_fetch_does_not_duplicate_pending_item(): void
    {
        Http::fake([
            'https://gov.example/*' => Http::response($this->sampleRss(), 200),
        ]);

        $source = ExternalNewsSource::query()->create([
            'name' => 'Gov',
            'slug' => 'gov',
            'type' => ExternalNewsSource::TYPE_RSS,
            'endpoint_url' => 'https://gov.example/feed.xml',
            'label_en' => 'Government',
            'label_ar' => 'حكومة',
            'is_active' => true,
            'fetch_interval_minutes' => 60,
            'priority' => 0,
        ]);

        $svc = app(ExternalNewsRssIngestionService::class);
        $svc->ingestFromSource($source);
        $svc->ingestFromSource($source);

        $this->assertSame(1, ExternalNewsItem::query()->where('source_id', $source->id)->count());
    }

    public function test_public_detail_is_hidden_until_published(): void
    {
        Http::fake([
            'https://gov.example/*' => Http::response($this->sampleRss(), 200),
        ]);

        $source = ExternalNewsSource::query()->create([
            'name' => 'Gov',
            'slug' => 'gov',
            'type' => ExternalNewsSource::TYPE_RSS,
            'endpoint_url' => 'https://gov.example/feed.xml',
            'label_en' => 'Government',
            'label_ar' => 'حكومة',
            'is_active' => true,
            'fetch_interval_minutes' => 60,
            'priority' => 0,
        ]);

        app(ExternalNewsRssIngestionService::class)->ingestFromSource($source);
        $item = ExternalNewsItem::query()->firstOrFail();

        $this->get(route('media.external.show', $item))->assertNotFound();

        $item->update([
            'status' => ExternalNewsItem::STATUS_PUBLISHED,
            'published_at' => now(),
            'show_in_media_center' => true,
            'show_on_home' => false,
        ]);

        $this->get(route('media.external.show', $item))->assertOk()->assertSee('Headline', false);
    }
}

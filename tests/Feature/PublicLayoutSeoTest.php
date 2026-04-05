<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLayoutSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_hub_emits_meta_description_and_open_graph(): void
    {
        $response = $this->get('/media');

        $response->assertOk();
        $response->assertSee(e(__('site.media_hub_meta_description')), false);
        $response->assertSee('property="og:url"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('rel="canonical"', false);
    }

    public function test_public_page_emits_open_graph_and_canonical(): void
    {
        $response = $this->get('/about?lang=en');

        $response->assertOk();
        $response->assertSee('property="og:url"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:description"', false);
        $response->assertSee('property="og:type"', false);
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('name="twitter:title"', false);
        $response->assertSee('name="twitter:description"', false);
    }

    public function test_youth_councils_page_emits_article_og_and_canonical(): void
    {
        $response = $this->get(route('youth-councils', ['lang' => 'en']));

        $response->assertOk();
        $response->assertSee('property="og:type" content="article"', false);
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('property="og:url"', false);
    }

    public function test_admin_cms_preview_skips_canonical_and_og_url(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $page = CmsPage::query()->create([
            'slug' => 'seo-preview-slug',
            'locale' => 'en',
            'title' => 'Preview SEO title',
            'body' => 'Body.',
            'status' => CmsPage::STATUS_DRAFT,
            'author_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.cms-pages.preview', $page));

        $response->assertOk();
        $response->assertDontSee('rel="canonical"', false);
        $response->assertDontSee('property="og:url"', false);
        $response->assertDontSee('application/ld+json', false);
        $response->assertSee('property="og:title"', false);
    }

    public function test_volunteer_opportunity_detail_emits_canonical_and_og_url(): void
    {
        $org = Organization::query()->create(['name_en' => 'SEO Org', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'SEO Opportunity Event',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $url = route('volunteer.opportunities.show', $event, true);
        $response = $this->get(route('volunteer.opportunities.show', $event));

        $response->assertOk();
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('property="og:url"', false);
        $this->assertStringContainsString($url, $response->getContent());
        $response->assertSee('property="og:type"', false);
        $response->assertSee('content="article"', false);
    }

    public function test_public_event_detail_emits_canonical_og_and_optional_default_image(): void
    {
        config(['swaeduae.default_og_image_url' => 'https://cdn.example/event-og.png']);

        $org = Organization::query()->create(['name_en' => 'Pub Org', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Public Calendar Event',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $response = $this->get(route('events.show', $event));

        $response->assertOk();
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('property="og:image"', false);
        $response->assertSee('https://cdn.example/event-og.png', false);
    }
}

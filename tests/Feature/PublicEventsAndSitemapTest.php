<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEventsAndSitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_index_lists_upcoming_events_from_database(): void
    {
        $event = Event::factory()->create([
            'title_en' => 'Unique Public Event Title XYZ',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);

        $this->get('/events')
            ->assertOk()
            ->assertViewIs('public.events-index')
            ->assertSee('Unique Public Event Title XYZ', false);

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertViewIs('public.events.show')
            ->assertSee('Unique Public Event Title XYZ', false);
    }

    public function test_events_index_search_filters_by_title(): void
    {
        Event::factory()->create([
            'title_en' => 'Alpha Public Calendar',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'title_en' => 'Beta Other Title',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);

        $this->get(route('events.index', ['q' => 'Alpha Public']))
            ->assertOk()
            ->assertSee('Alpha Public Calendar', false)
            ->assertDontSee('Beta Other Title', false);
    }

    public function test_events_index_search_matches_host_organization_name(): void
    {
        $orgMatch = Organization::query()->create([
            'name_en' => 'Coastal Volunteers Guild UniquePublic',
            'name_ar' => null,
        ]);
        $orgOther = Organization::query()->create([
            'name_en' => 'Other Public Host',
            'name_ar' => null,
        ]);

        Event::factory()->create([
            'organization_id' => $orgMatch->id,
            'title_en' => 'Beach Day Generic Title',
            'title_ar' => null,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'organization_id' => $orgOther->id,
            'title_en' => 'Park Day Generic Title',
            'title_ar' => null,
            'event_starts_at' => now()->addDays(3),
            'event_ends_at' => now()->addDays(4),
        ]);

        $this->get(route('events.index', ['q' => 'Volunteers Guild']))
            ->assertOk()
            ->assertSee('Beach Day Generic Title', false)
            ->assertDontSee('Park Day Generic Title', false);
    }

    public function test_events_index_search_matches_title_with_underscore(): void
    {
        $org = Organization::query()->create(['name_en' => 'Public Underscore Org', 'name_ar' => null]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'City_Walk_2026_PublicUnique',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Other Public Event',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);

        $this->get(route('events.index', ['q' => 'City_Walk']))
            ->assertOk()
            ->assertSee('City_Walk_2026_PublicUnique', false)
            ->assertDontSee('Other Public Event', false);
    }

    public function test_events_index_is_paginated(): void
    {
        for ($i = 0; $i < 16; $i++) {
            Event::factory()->create([
                'title_en' => 'Public Paginate '.$i,
                'event_starts_at' => now()->addDays($i + 1),
                'event_ends_at' => now()->addDays($i + 2),
            ]);
        }

        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee('Public Paginate 0', false)
            ->assertSee('Public Paginate 14', false)
            ->assertDontSee('Public Paginate 15', false);

        $this->get(route('events.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Public Paginate 15', false);
    }

    public function test_events_index_rejects_oversized_search_query(): void
    {
        $this->get(route('events.index', ['q' => str_repeat('x', 121)]))
            ->assertSessionHasErrors('q');
    }

    public function test_events_index_merges_published_cms_intro_with_calendar(): void
    {
        $user = User::factory()->create();
        CmsPage::query()->create([
            'slug' => 'events',
            'locale' => 'en',
            'title' => 'CMS Events Heading',
            'body' => 'Intro from CMS.',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'author_id' => $user->id,
        ]);

        $this->get('/events')
            ->assertOk()
            ->assertViewIs('public.events-index')
            ->assertSee('CMS Events Heading', false)
            ->assertSee('Intro from CMS.', false);
    }

    public function test_sitemap_xml_lists_core_routes_and_upcoming_event(): void
    {
        $event = Event::factory()->create([
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);

        $response = $this->get('/sitemap.xml');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $body = $response->getContent();
        $this->assertStringContainsString(route('home', [], true), $body);
        $this->assertStringContainsString(route('leadership', [], true), $body);
        $this->assertStringContainsString(route('events.index', [], true), $body);
        $this->assertStringContainsString(route('gallery', [], true), $body);
        $this->assertStringContainsString(route('events.show', $event, true), $body);
        $this->assertStringContainsString(route('volunteer.opportunities.show', $event, true), $body);

        $event->refresh();
        $this->assertStringContainsString(
            '<lastmod>'.$event->updated_at->timezone('UTC')->toDateString().'</lastmod>',
            $body
        );
    }

    public function test_robots_txt_lists_sitemap(): void
    {
        $response = $this->get('/robots.txt');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('Sitemap: '.route('sitemap', [], true), false);
    }

    public function test_robots_txt_disallows_private_app_paths(): void
    {
        $response = $this->get('/robots.txt');
        $response->assertOk();
        $body = $response->getContent();
        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Disallow: /dashboard', $body);
        $this->assertStringContainsString('Disallow: /profile', $body);
        $this->assertStringContainsString('Disallow: /organization/', $body);
        $this->assertStringNotContainsString("Disallow: /register\n", $body);
    }
}

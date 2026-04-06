<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use App\Support\PublicLocale;
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
            ->assertSee('Unique Public Event Title XYZ', false)
            ->assertSee('data-testid="events-list-volunteer-link"', false)
            ->assertSee('data-testid="events-footer-opportunities"', false)
            ->assertSee(route('volunteer.opportunities.index', PublicLocale::query(), true), false);

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertViewIs('public.events.show')
            ->assertSee('Unique Public Event Title XYZ', false)
            ->assertSee('data-testid="public-event-view-opportunity"', false)
            ->assertSee('data-testid="public-event-copy-link"', false)
            ->assertSee('data-testid="public-event-download-ics"', false)
            ->assertSee(
                route('volunteer.opportunities.show', array_merge(['event' => $event], PublicLocale::query()), false),
                false
            );

        $ics = $this->get(route('events.ics', $event));
        $ics->assertOk();
        $ics->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $body = (string) $ics->getContent();
        $this->assertStringContainsString('BEGIN:VCALENDAR', $body);
        $this->assertStringContainsString('BEGIN:VEVENT', $body);
        $this->assertStringContainsString('DTSTART:', $body);
        $this->assertStringContainsString('DTEND:', $body);
        $this->assertStringContainsString('SUMMARY:Unique Public Event Title XYZ', $body);

        $this->get(route('events.show', ['event' => $event, 'lang' => 'ar']))
            ->assertOk()
            ->assertSee(
                route('volunteer.opportunities.show', array_merge(['event' => $event, 'lang' => 'ar']), false),
                false
            );
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

    public function test_events_index_when_empty_shows_opportunities_footer_cta(): void
    {
        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee(__('No upcoming events listed yet.'), false)
            ->assertSee('data-testid="events-footer-opportunities"', false)
            ->assertSee(route('volunteer.opportunities.index', PublicLocale::query(), true), false);
    }

    public function test_events_index_sorts_by_title_when_requested(): void
    {
        $org = Organization::query()->create(['name_en' => 'Sort Org Public', 'name_ar' => null]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Zebra public calendar sort',
            'title_ar' => null,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Apple public calendar sort',
            'title_ar' => null,
            'event_starts_at' => now()->addDays(3),
            'event_ends_at' => now()->addDays(4),
        ]);

        $html = $this->get(route('events.index', ['sort' => 'title_asc']))
            ->assertOk()
            ->getContent();
        $this->assertNotFalse($html);
        $posApple = strpos($html, 'Apple public calendar sort');
        $posZebra = strpos($html, 'Zebra public calendar sort');
        $this->assertNotFalse($posApple);
        $this->assertNotFalse($posZebra);
        $this->assertLessThan($posZebra, $posApple);
    }

    public function test_events_index_sort_starts_desc_puts_later_event_first(): void
    {
        $org = Organization::query()->create(['name_en' => 'Sort Org Desc', 'name_ar' => null]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Earlier slot unique title',
            'title_ar' => null,
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Later slot unique title',
            'title_ar' => null,
            'event_starts_at' => now()->addWeek(),
            'event_ends_at' => now()->addWeeks(2),
        ]);

        $html = $this->get(route('events.index', ['sort' => 'starts_desc']))
            ->assertOk()
            ->getContent();
        $this->assertNotFalse($html);
        $posLater = strpos($html, 'Later slot unique title');
        $posEarlier = strpos($html, 'Earlier slot unique title');
        $this->assertNotFalse($posLater);
        $this->assertNotFalse($posEarlier);
        $this->assertLessThan($posEarlier, $posLater);
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
            ->assertSee('Intro from CMS.', false)
            ->assertSee('"@type":"Article"', false)
            ->assertSee('property="og:type" content="article"', false);
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
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertNotNull($cacheControl);
        $this->assertStringContainsString('max-age=3600', $cacheControl);
        $this->assertStringContainsString('public', $cacheControl);

        $body = $response->getContent();
        $this->assertStringContainsString(route('home', [], true), $body);
        $this->assertStringContainsString(route('feed', [], true), $body);
        $this->assertStringContainsString(route('site.humans', [], true), $body);
        $this->assertStringContainsString(route('site.security', [], true), $body);
        $this->assertStringContainsString(route('site.favicon', [], true), $body);
        $this->assertStringContainsString(route('site.webmanifest', [], true), $body);
        $this->assertStringContainsString(route('leadership', [], true), $body);
        $this->assertStringContainsString(route('events.index', [], true), $body);
        $this->assertStringContainsString(route('gallery', [], true), $body);
        $this->assertStringContainsString(route('events.show', $event, true), $body);
        $this->assertStringContainsString(route('volunteer.opportunities.show', $event, true), $body);
        $this->assertStringContainsString(route('volunteer.opportunities.feed', [], true), $body);

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
        $robotsCache = $response->headers->get('Cache-Control');
        $this->assertNotNull($robotsCache);
        $this->assertStringContainsString('max-age=3600', $robotsCache);
        $this->assertStringContainsString('public', $robotsCache);
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

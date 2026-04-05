<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeUpcomingEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_lists_upcoming_events_from_database(): void
    {
        $org = Organization::factory()->create(['name_en' => 'Home Test Org']);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Unique Home Calendar Event',
            'title_ar' => null,
            'event_starts_at' => now()->addDays(1),
            'event_ends_at' => now()->addDays(1)->addHours(2),
            'application_required' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Unique Home Calendar Event', false);
        $response->assertSee('Home Test Org', false);
        $response->assertSee(route('events.show', $event), false);
    }

    public function test_home_shows_empty_state_when_no_upcoming_events(): void
    {
        Event::factory()->create([
            'event_starts_at' => now()->subDays(5),
            'event_ends_at' => now()->subDays(5)->addHour(),
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(__('No upcoming events listed yet.'), false);
    }

    public function test_home_emits_default_og_image_when_configured(): void
    {
        config(['swaeduae.default_og_image_url' => 'https://cdn.example/home-og.png']);

        $this->get('/')
            ->assertOk()
            ->assertSee('<meta property="og:image" content="https://cdn.example/home-og.png">', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false);
    }
}

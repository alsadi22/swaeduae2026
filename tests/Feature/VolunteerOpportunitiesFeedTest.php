<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolunteerOpportunitiesFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_opportunities_atom_feed_lists_upcoming_events(): void
    {
        $org = Organization::query()->create(['name_en' => 'Feed Org', 'name_ar' => null]);
        $event = Event::factory()->create([
            'organization_id' => $org->id,
            'title_en' => 'Feed Opportunity Unique',
            'event_starts_at' => now()->addDay(),
            'event_ends_at' => now()->addDays(2),
            'checkin_window_starts_at' => now(),
            'checkin_window_ends_at' => now()->addDays(2),
        ]);

        $response = $this->get('/feeds/volunteer-opportunities.atom');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/atom+xml; charset=UTF-8');
        $body = $response->getContent();
        $this->assertStringContainsString('Feed Opportunity Unique', $body);
        $this->assertStringContainsString(route('volunteer.opportunities.show', $event, true), $body);
    }
}

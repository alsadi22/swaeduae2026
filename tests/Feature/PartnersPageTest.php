<?php

namespace Tests\Feature;

use App\Support\PublicLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnersPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_partners_page_shows_placeholder_when_config_empty(): void
    {
        config(['swaeduae.home_partners' => []]);

        $this->get('/partners')
            ->assertOk()
            ->assertSeeText(__('site.partners_strip'));
    }

    public function test_partners_page_shows_config_logos_when_set(): void
    {
        config([
            'swaeduae.home_partners' => [
                [
                    'label' => 'Partner Alpha Co',
                    'label_ar' => 'شريك ألفا',
                    'url' => 'https://example.org/partner',
                    'logo' => '/images/partners/.gitkeep',
                ],
            ],
        ]);

        $this->get('/partners')
            ->assertOk()
            ->assertSeeText('Partner Alpha Co');
    }

    public function test_partners_page_includes_opportunities_footer_link(): void
    {
        config(['swaeduae.home_partners' => []]);

        $this->get('/partners')
            ->assertOk()
            ->assertSee('data-testid="partners-footer-opportunities"', false)
            ->assertSee(route('volunteer.opportunities.index', PublicLocale::query(), true), false);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_page_lists_items_from_site_translations(): void
    {
        $items = trans('site.faq_items');
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        $firstQ = $items[0]['question'] ?? '';

        $this->get('/faq')
            ->assertOk()
            ->assertSee($firstQ, false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('"@type":"Question"', false);
    }

    public function test_faq_page_has_meta_description_from_site(): void
    {
        $this->get('/faq')
            ->assertOk()
            ->assertSee(__('site.faq_meta_description'), false);
    }
}

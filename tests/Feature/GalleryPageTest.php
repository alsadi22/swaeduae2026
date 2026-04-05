<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_page_renders(): void
    {
        $this->get('/gallery')->assertOk()->assertSeeText(__('Gallery'));
    }

    public function test_gallery_lists_config_document_downloads_when_set(): void
    {
        config([
            'swaeduae.document_downloads' => [
                ['label' => 'Test annual PDF', 'label_ar' => null, 'url' => 'https://example.org/report.pdf'],
            ],
        ]);

        $this->get('/gallery')
            ->assertOk()
            ->assertSeeText('Test annual PDF')
            ->assertSeeText(__('Reports and downloads'));
    }
}

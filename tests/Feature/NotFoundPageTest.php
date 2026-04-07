<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotFoundPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_path_returns_branded_404(): void
    {
        $response = $this->get('/path-that-does-not-exist-swaeduae-404');

        $response->assertNotFound();
        $response->assertSeeText(__('Page not found'));
        $response->assertSeeText(__('The page you are looking for does not exist or may have been moved.'));
        $response->assertSeeText(__('Back to home'));
        $response->assertSee('data-testid="error-404-copy-page-url"', false);
        $response->assertSee('data-testid="error-404-opportunities"', false);
        $response->assertSee('data-testid="error-404-events"', false);
        $response->assertSee('data-testid="error-404-support"', false);
        $response->assertSee('topic=other', false);
    }
}

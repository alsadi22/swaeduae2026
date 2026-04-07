<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BrandedHttpErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_branded_419_view_includes_copy_link_and_recovery_actions(): void
    {
        $html = view('errors.419')->render();

        $this->assertStringContainsString('data-testid="error-419-copy-page-url"', $html);
        $this->assertStringContainsString(e(__('Page expired')), $html);
        $this->assertStringContainsString(e(__('HTTP 419 explanation')), $html);
    }

    public function test_maintenance_mode_renders_branded_503_page(): void
    {
        Artisan::call('down', ['--render' => 'errors::503']);

        try {
            $this->get('/')
                ->assertStatus(503)
                ->assertSee('data-testid="error-503-copy-page-url"', false)
                ->assertSee(__('Temporarily unavailable'), false);
        } finally {
            Artisan::call('up');
        }
    }
}

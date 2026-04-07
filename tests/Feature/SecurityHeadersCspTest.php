<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SecurityHeadersCspTest extends TestCase
{
    use RefreshDatabase;

    public function test_csp_report_only_header_set_when_configured(): void
    {
        Config::set('swaeduae.security.csp_report_only', "default-src 'self'");

        $this->get('/')
            ->assertOk()
            ->assertHeader('Content-Security-Policy-Report-Only', "default-src 'self'");
    }

    public function test_csp_report_only_header_omitted_when_empty(): void
    {
        Config::set('swaeduae.security.csp_report_only', '');

        $response = $this->get('/');
        $response->assertOk();
        $this->assertFalse($response->headers->has('Content-Security-Policy-Report-Only'));
    }
}

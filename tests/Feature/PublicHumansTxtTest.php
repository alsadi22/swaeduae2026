<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHumansTxtTest extends TestCase
{
    use RefreshDatabase;

    public function test_humans_txt_is_plain_text_with_core_links(): void
    {
        $response = $this->get('/humans.txt');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $body = $response->getContent();
        $this->assertStringContainsString('SwaedUAE', $body);
        $this->assertStringContainsString(route('contact.show', [], true), $body);
        $this->assertStringContainsString(route('feed', [], true), $body);
        $this->assertStringContainsString(route('sitemap', [], true), $body);
        $this->assertStringContainsString(route('register.volunteer', [], true), $body);
        $this->assertStringContainsString(route('register.organization', [], true), $body);
        $this->assertStringContainsString('/.well-known/security.txt', $body);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSecurityTxtTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_txt_is_plain_text_with_contact(): void
    {
        $response = $this->get('/.well-known/security.txt');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $body = $response->getContent();
        $this->assertStringContainsString('Contact: mailto:'.config('swaeduae.mail.support'), $body);
        $this->assertStringContainsString('Preferred-Languages: en, ar', $body);
        $this->assertStringContainsString('Expires:', $body);
    }
}

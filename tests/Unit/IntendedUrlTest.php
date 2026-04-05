<?php

namespace Tests\Unit;

use App\Support\IntendedUrl;
use Tests\TestCase;

class IntendedUrlTest extends TestCase
{
    public function test_sanitize_accepts_leading_slash_path(): void
    {
        $this->assertNotNull(IntendedUrl::sanitize('/volunteer/opportunities'));
    }

    public function test_sanitize_rejects_protocol_relative(): void
    {
        $this->assertNull(IntendedUrl::sanitize('//evil.example/phish'));
    }

    public function test_sanitize_rejects_foreign_https_url(): void
    {
        $this->assertNull(IntendedUrl::sanitize('https://evil.example/'));
    }

    public function test_query_params_for_relative_uri_empty_when_invalid(): void
    {
        $this->assertSame([], IntendedUrl::queryParamsForRelativeUri(''));
        $this->assertSame([], IntendedUrl::queryParamsForRelativeUri('https://other.test/x'));
    }

    public function test_query_params_for_relative_uri_returns_return_key(): void
    {
        $this->assertSame(['return' => '/volunteer?lang=ar'], IntendedUrl::queryParamsForRelativeUri('/volunteer?lang=ar'));
    }
}

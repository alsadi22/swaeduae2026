<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\PublicLocale;
use Illuminate\Http\Request;
use Tests\TestCase;

class PublicLocaleTest extends TestCase
{
    public function test_query_for_user_returns_preferred_locale_when_valid(): void
    {
        $user = new User(['locale_preferred' => 'ar']);

        $this->assertSame(['lang' => 'ar'], PublicLocale::queryForUser($user));
    }

    public function test_query_for_user_falls_back_to_app_locale_when_user_null(): void
    {
        $this->assertSame(['lang' => app()->getLocale()], PublicLocale::queryForUser(null));
    }

    public function test_query_from_request_or_user_prefers_valid_request_lang_over_user_preference(): void
    {
        $this->app->instance('request', Request::create('https://example.test/o', 'GET', ['lang' => 'ar']));
        $user = new User(['locale_preferred' => 'en']);

        $this->assertSame(['lang' => 'ar'], PublicLocale::queryFromRequestOrUser($user));
    }

    public function test_query_from_request_or_user_ignores_invalid_request_lang(): void
    {
        $this->app->instance('request', Request::create('https://example.test/o', 'GET', ['lang' => 'xx']));
        $user = new User(['locale_preferred' => 'ar']);

        $this->assertSame(['lang' => 'ar'], PublicLocale::queryFromRequestOrUser($user));
    }
}

<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\PublicLocale;
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
}

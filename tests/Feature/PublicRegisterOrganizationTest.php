<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRegisterOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_organization_page_renders(): void
    {
        $this->get(route('register.organization'))
            ->assertOk()
            ->assertSee(__('Register Organization'), false)
            ->assertSee(__('Organization name (English)'), false)
            ->assertSee(__('Submit organization registration'), false);
    }
}

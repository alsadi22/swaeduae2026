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

    public function test_register_organization_includes_opportunities_footer_with_locale(): void
    {
        $oppAr = route('volunteer.opportunities.index', ['lang' => 'ar'], false);

        $this->get(route('register.organization', ['lang' => 'ar']))
            ->assertOk()
            ->assertSee('data-testid="register-organization-footer-opportunities"', false)
            ->assertSee($oppAr, false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalDataRightsStripTest extends TestCase
{
    use RefreshDatabase;

    public function test_terms_page_includes_data_rights_strip_for_guests(): void
    {
        $this->get('/legal/terms')
            ->assertOk()
            ->assertSee('data-testid="legal-data-rights-strip"', false)
            ->assertSee(route('login', ['lang' => 'en'], true), false);
    }

    public function test_privacy_page_data_rights_strip_links_to_profile_when_authenticated(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)
            ->get('/legal/privacy')
            ->assertOk()
            ->assertSee('data-testid="legal-data-rights-strip"', false)
            ->assertSee(route('profile.edit', ['lang' => 'en'], true), false);
    }
}

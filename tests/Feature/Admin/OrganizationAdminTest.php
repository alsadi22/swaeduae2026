<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationAdminTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoles(): void
    {
        $this->seed(RoleSeeder::class);
    }

    private function adminUser(): User
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_guest_redirected_from_admin_organizations_index(): void
    {
        $this->get('/admin/organizations')->assertRedirect(route('admin.login'));
    }

    public function test_volunteer_cannot_access_admin_organizations(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $this->actingAs($user)->get('/admin/organizations')->assertForbidden();
    }

    public function test_admin_can_view_organizations_index(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)->get('/admin/organizations')->assertOk();
    }

    public function test_admin_can_create_organization(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post('/admin/organizations', [
            'name_en' => 'SwaedUAE Demo Org',
            'name_ar' => 'تجريبي',
        ]);

        $response->assertRedirect(route('admin.organizations.index'));
        $this->assertDatabaseHas('organizations', [
            'name_en' => 'SwaedUAE Demo Org',
            'name_ar' => 'تجريبي',
            'verification_status' => Organization::VERIFICATION_APPROVED,
        ]);
    }

    public function test_admin_can_update_organization(): void
    {
        $user = $this->adminUser();
        $org = Organization::factory()->create(['name_en' => 'Old']);

        $response = $this->actingAs($user)->put('/admin/organizations/'.$org->id, [
            'name_en' => 'New name',
            'name_ar' => null,
        ]);

        $response->assertRedirect(route('admin.organizations.index'));
        $this->assertDatabaseHas('organizations', [
            'id' => $org->id,
            'name_en' => 'New name',
        ]);
    }

    public function test_admin_can_delete_organization_with_no_events(): void
    {
        $user = $this->adminUser();
        $org = Organization::factory()->create();

        $response = $this->actingAs($user)->delete('/admin/organizations/'.$org->id);

        $response->assertRedirect(route('admin.organizations.index'));
        $this->assertDatabaseMissing('organizations', ['id' => $org->id]);
    }

    public function test_admin_cannot_delete_organization_with_events(): void
    {
        $user = $this->adminUser();
        $org = Organization::factory()->create();
        Event::factory()->create(['organization_id' => $org->id]);

        $response = $this->actingAs($user)->delete('/admin/organizations/'.$org->id);

        $response->assertRedirect(route('admin.organizations.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('organizations', ['id' => $org->id]);
    }

    public function test_admin_organizations_index_search_filters_by_name(): void
    {
        $user = $this->adminUser();
        Organization::factory()->create(['name_en' => 'ZetaUniqueOrgSearchXYZ']);
        Organization::factory()->create(['name_en' => 'Other Org Name']);

        $this->actingAs($user)
            ->get(route('admin.organizations.index', ['search' => 'ZetaUniqueOrgSearch']))
            ->assertOk()
            ->assertSee('ZetaUniqueOrgSearchXYZ', false)
            ->assertDontSee('Other Org Name', false);
    }

    public function test_admin_organizations_index_search_matches_name_with_underscore(): void
    {
        $user = $this->adminUser();
        Organization::factory()->create(['name_en' => 'Emirates_Culture_Guild_UniqueX']);
        Organization::factory()->create(['name_en' => 'Plain Other Org']);

        $this->actingAs($user)
            ->get(route('admin.organizations.index', ['search' => 'Culture_Guild']))
            ->assertOk()
            ->assertSee('Emirates_Culture_Guild_UniqueX', false)
            ->assertDontSee('Plain Other Org', false);
    }

    public function test_admin_organizations_index_search_max_length_validation(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('admin.organizations.index', ['search' => str_repeat('a', 101)]))
            ->assertSessionHasErrors('search');
    }
}

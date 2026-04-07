<?php

namespace Tests\Feature\Admin;

use App\Models\GalleryImage;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBrandingAndGalleryTest extends TestCase
{
    use RefreshDatabase;

    /** Minimal valid 1×1 PNG (no GD required). */
    private function tinyPng(): UploadedFile
    {
        $binary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);

        return UploadedFile::fake()->createWithContent('logo.png', $binary);
    }

    private function adminUser(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_guest_cannot_access_site_settings(): void
    {
        $this->get('/admin/site-settings/edit')->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_save_hero_and_logo(): void
    {
        Storage::fake('public');
        $user = $this->adminUser();
        $file = $this->tinyPng();

        $this->actingAs($user)
            ->put('/admin/site-settings', [
                'hero_mission_en' => 'Custom EN headline',
                'hero_mission_ar' => '',
                'hero_subline_en' => 'Custom EN sub',
                'hero_subline_ar' => null,
                'header_logo' => $file,
            ])
            ->assertRedirect();

        $setting = SiteSetting::query()->first();
        $this->assertNotNull($setting);
        $this->assertSame('Custom EN headline', $setting->hero_mission_en);
        $this->assertNull($setting->hero_mission_ar);
        $this->assertNotNull($setting->header_logo_path);
        Storage::disk('public')->assertExists($setting->header_logo_path);

        $this->get('/')
            ->assertOk()
            ->assertSee('Custom EN headline', false)
            ->assertSee('Custom EN sub', false);
    }

    public function test_admin_can_upload_gallery_image_and_public_gallery_shows_it(): void
    {
        Storage::fake('public');
        $user = $this->adminUser();
        $image = $this->tinyPng();

        $response = $this->actingAs($user)
            ->post('/admin/gallery-images', [
                'image' => $image,
                'alt_text_en' => 'Community hall',
                'alt_text_ar' => 'قاعة',
                'is_visible' => '1',
            ]);

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertIsString($location);
        $this->assertStringContainsString('/admin/gallery-images', $location);

        $this->assertDatabaseCount('gallery_images', 1);
        $row = GalleryImage::query()->first();
        $this->assertNotNull($row);
        Storage::disk('public')->assertExists($row->path);

        $this->get('/gallery')
            ->assertOk()
            ->assertSee('Community hall', false)
            ->assertSee(route('gallery', [], false), false);
    }
}

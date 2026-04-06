<?php

namespace Tests\Feature;

use App\Mail\OrganizationRegistrationStaffMail;
use App\Models\Organization;
use App\Models\User;
use App\Support\PublicLocale;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrganizationRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_organization_registration(): void
    {
        Notification::fake();
        Mail::fake();

        $this->seed(RoleSeeder::class);

        $response = $this->post(route('register.organization.store'), [
            'name_en' => 'Hope Charity',
            'name_ar' => null,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'email' => 'owner@example.org',
            'phone' => '+971501112233',
            'password' => 'password',
            'password_confirmation' => 'password',
            'locale_preferred' => 'en',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('verification.notice', PublicLocale::query(), false));
        $this->assertAuthenticated();

        $org = Organization::query()->where('name_en', 'Hope Charity')->first();
        $this->assertNotNull($org);
        $this->assertTrue($org->isPendingVerification());

        $user = User::query()->where('email', 'owner@example.org')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('org-owner'));
        $this->assertSame($org->id, $user->organization_id);
        $this->assertSame($user->id, $org->fresh()->registered_by_user_id);

        Notification::assertSentTo($user, VerifyEmail::class);

        Mail::assertQueued(OrganizationRegistrationStaffMail::class, function (OrganizationRegistrationStaffMail $mail) use ($org, $user): bool {
            if (! $mail->hasTo('admin@testing.test')
                || ! $mail->organization->is($org)
                || ! $mail->registeringUser->is($user)) {
                return false;
            }

            $html = $mail->render();
            $adminOrgsUrl = route('admin.organizations.index', PublicLocale::query(), true);

            return str_contains($html, $adminOrgsUrl);
        });
    }

    public function test_organization_registration_redirect_uses_preferred_locale_on_verification_notice(): void
    {
        Notification::fake();
        Mail::fake();

        $this->seed(RoleSeeder::class);

        $this->post(route('register.organization.store'), [
            'name_en' => 'Arabic Pref Org',
            'name_ar' => null,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'email' => 'ar-pref-owner@example.org',
            'phone' => '+971501112233',
            'password' => 'password',
            'password_confirmation' => 'password',
            'locale_preferred' => 'ar',
            'terms' => '1',
        ])
            ->assertRedirect(route('verification.notice', ['lang' => 'ar'], false))
            ->assertSessionHasNoErrors();
    }

    public function test_authenticated_user_is_redirected_from_organization_registration_post(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('volunteer');

        $response = $this->actingAs($user)->post(route('register.organization.store'), [
            'name_en' => 'X',
            'first_name' => 'A',
            'last_name' => 'B',
            'email' => 'unique@example.org',
            'phone' => '+971500000000',
            'password' => 'password',
            'password_confirmation' => 'password',
            'locale_preferred' => 'en',
            'terms' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('organizations', ['name_en' => 'X']);
    }

    public function test_organization_registration_is_throttled_per_ip(): void
    {
        Notification::fake();
        Mail::fake();
        $this->seed(RoleSeeder::class);

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('register.organization.store'), [
                'name_en' => 'Throttle Org '.$i,
                'name_ar' => null,
                'first_name' => 'Sam',
                'last_name' => 'Owner'.$i,
                'email' => "throttle-owner-{$i}@example.org",
                'phone' => '+971501112233',
                'password' => 'password',
                'password_confirmation' => 'password',
                'locale_preferred' => 'en',
                'terms' => '1',
            ])->assertRedirect(route('verification.notice', PublicLocale::query(), false));
            Auth::logout();
            $this->flushSession();
        }

        $this->post(route('register.organization.store'), [
            'name_en' => 'Too Many Registrations',
            'name_ar' => null,
            'first_name' => 'Sam',
            'last_name' => 'Blocked',
            'email' => 'throttle-owner-10@example.org',
            'phone' => '+971501112233',
            'password' => 'password',
            'password_confirmation' => 'password',
            'locale_preferred' => 'en',
            'terms' => '1',
        ])->assertStatus(429);
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationNotificationThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_notification_resend_is_throttled_per_user(): void
    {
        $user = User::factory()->unverified()->create();

        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($user)
                ->from(route('verification.notice'))
                ->post(route('verification.send'))
                ->assertRedirect();
        }

        $this->actingAs($user)
            ->from(route('verification.notice'))
            ->post(route('verification.send'))
            ->assertStatus(429);
    }
}

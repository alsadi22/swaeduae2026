<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationRequiredTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('verification.notice', absolute: false));
    }
}

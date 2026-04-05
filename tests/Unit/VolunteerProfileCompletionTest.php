<?php

namespace Tests\Unit;

use App\Models\VolunteerProfile;
use App\Support\VolunteerProfileCompletion;
use PHPUnit\Framework\TestCase;

class VolunteerProfileCompletionTest extends TestCase
{
    public function test_empty_profile_scores_zero(): void
    {
        $profile = new VolunteerProfile;

        $this->assertSame(0, VolunteerProfileCompletion::percent($profile));
    }

    public function test_full_profile_scores_one_hundred(): void
    {
        $profile = new VolunteerProfile([
            'bio' => str_repeat('a', 20),
            'emergency_contact_name' => 'Jane',
            'emergency_contact_phone' => '+971500000000',
            'skills' => 'First aid',
            'availability' => 'Weekends',
        ]);

        $this->assertSame(100, VolunteerProfileCompletion::percent($profile));
    }
}

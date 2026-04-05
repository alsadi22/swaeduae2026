<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Baseline roles aligned with the SwaedUAE PRD (expand permissions later).
     */
    public function run(): void
    {
        foreach ([
            'super-admin',
            'admin',
            'volunteer',
            'org-owner',
            'org-manager',
            'org-coordinator',
            'org-viewer',
        ] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}

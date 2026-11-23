<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'manage-projects',
            'create-projects',
            'edit-projects',
            'delete-projects',
            'manage-tasks',
            'create-tasks',
            'assign-tasks',
            'review-tasks',
            'delete-tasks',
            'view-reports',
            'generate-reports',
            'manage-users',
            'schedule-meetings',
            'use-ai-assistant',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $admin = Role::findOrCreate('admin', 'web');
        $admin->syncPermissions(Permission::all());

        $manager = Role::findOrCreate('manager', 'web');
        $manager->syncPermissions([
            'create-projects',
            'edit-projects',
            'manage-tasks',
            'create-tasks',
            'assign-tasks',
            'review-tasks',
            'view-reports',
            'generate-reports',
            'schedule-meetings',
            'use-ai-assistant',
        ]);

        $member = Role::findOrCreate('member', 'web');
        $member->syncPermissions([
            'create-tasks',
            'view-reports',
            'schedule-meetings',
            'use-ai-assistant',
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'team.update',
            'team.invite',
            'team.remove-member',
            'billing.manage',
            'ai.generate',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $owner = Role::findOrCreate('team-owner', 'web');
        $admin = Role::findOrCreate('admin', 'web');
        $member = Role::findOrCreate('member', 'web');

        $owner->syncPermissions($permissions);
        $admin->syncPermissions(['team.update', 'team.invite', 'ai.generate']);
        $member->syncPermissions(['ai.generate']);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Create role (set guard_name explicitly to avoid guard mismatch)
        $role = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]); // [web:450]

        // Create or update user
        $user = User::updateOrCreate(
            ['email' => 'super_admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
            ]
        ); // [web:455]

        // Assign role to user
        $user->syncRoles([$role]);
    }
}

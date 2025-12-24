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
        app(PermissionRegistrar::class)->forgetCachedPermissions(); // [web:454]

        // Create role (set guard_name explicitly to avoid guard mismatch)
        $role = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]); // [web:450]

        // Create or update user
        $user = User::updateOrCreate(
            ['email' => 'super_admin@admin.com'], // jangan pakai [ ] [web:455]
            [
                'name' => 'Super Admin',
                'password' => 'password', // akan di-hash otomatis karena casts() = 'hashed'
            ]
        ); // [web:455]

        // Assign role to user
        $user->syncRoles([$role]); // lebih aman daripada assignRole berulang-ulang [web:431]
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        $roles = [
            'super_admin',
            'admin',
            'author',
            'reviewer',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => $guard,
            ]);
        }

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'super_admin@admin.com',
                'password' => 'password',
                'role' => 'super_admin',
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => 'password',
                'role' => 'admin',
            ],
            [
                'name' => 'Author',
                'email' => 'author@admin.com',
                'password' => 'password',
                'role' => 'author',
            ],
            [
                'name' => 'Reviewer',
                'email' => 'reviewer@admin.com',
                'password' => 'password',
                'role' => 'reviewer',
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                ]
            );

            $role = Role::findOrCreate($data['role'], $guard);

            $user->syncRoles([$role]);
        }
    }
}

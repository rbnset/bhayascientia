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
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        // Create roles
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

        // Create users with complete attributes
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'super_admin@admin.com',
                'password' => 'password',
                'whatsapp_number' => '+62812345678901',
                'job_title' => 'Super Administrator',
                'role' => 'super_admin',
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => 'password',
                'whatsapp_number' => '+62812345678902',
                'job_title' => 'Administrator',
                'role' => 'admin',
            ],
            [
                'name' => 'Author',
                'email' => 'author@admin.com',
                'password' => 'password',
                'whatsapp_number' => '+62812345678903',
                'job_title' => 'Content Author',
                'role' => 'author',
            ],
            [
                'name' => 'Reviewer',
                'email' => 'reviewer@admin.com',
                'password' => 'password',
                'whatsapp_number' => '+62812345678904',
                'job_title' => 'Manuscript Reviewer',
                'role' => 'reviewer',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'whatsapp_number' => $data['whatsapp_number'] ?? null,
                    'job_title' => $data['job_title'] ?? null,
                ]
            );

            $user->syncRoles([$role]);
        }

        // Reset cache again after assigning roles
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

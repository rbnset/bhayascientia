<?php

namespace Database\Seeders;

use App\Models\Author;
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

        $roles = ['super_admin', 'admin', 'author', 'reviewer'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => $guard,
            ]);
        }

        $users = [
            [
                'name'            => 'Super Admin',
                'email'           => 'super_admin@admin.com',
                'password'        => 'password',
                'whatsapp_number' => '+62812345678901',
                'job_title'       => 'Super Administrator',
                'role'            => 'super_admin',
            ],
            [
                'name'            => 'Admin',
                'email'           => 'admin@admin.com',
                'password'        => 'password',
                'whatsapp_number' => '+62812345678902',
                'job_title'       => 'Administrator',
                'role'            => 'admin',
            ],
            [
                'name'            => 'Author',
                'email'           => 'author@admin.com',
                'password'        => 'password',
                'whatsapp_number' => '+62812345678903',
                'job_title'       => 'Content Author',
                'role'            => 'author',
            ],
            [
                'name'            => 'Reviewer',
                'email'           => 'reviewer@admin.com',
                'password'        => 'password',
                'whatsapp_number' => '+62812345678904',
                'job_title'       => 'Manuscript Reviewer',
                'role'            => 'reviewer',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'            => $data['name'],
                    'password'        => Hash::make($data['password']),
                    'whatsapp_number' => $data['whatsapp_number'] ?? null,
                    'job_title'       => $data['job_title'] ?? null,
                ]
            );

            // ✅ Pakai syncRoles dari Spatie langsung (tidak override)
            $user->syncRoles([$role]);

            // ✅ Buat Author profile jika role = author dan belum ada
            if ($role === 'author' && !$user->authorProfile()->exists()) {
                Author::create([
                    'user_id'     => $user->id,
                    'name'        => null,
                    'email'       => null,
                    'affiliation' => null,
                    'bio'         => null,
                    'photo_path'  => null,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('✅ Roles & Users selesai dibuat.');
        $this->command->info('✅ Author profile otomatis dibuat untuk user dengan role author.');
    }
}

<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Membuat authors dari existing users...');

        // Buat authors dari existing users dengan role 'author'
        $authorUsers = User::role('author')->get();

        if ($authorUsers->isNotEmpty()) {
            foreach ($authorUsers as $user) {
                Author::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name' => $user->name,
                        'email' => $user->email,
                        'affiliation' => $user->job_title ?? 'Independent Researcher',
                        'bio' => "Bio for {$user->name}. Experienced researcher with expertise in various scientific fields.",
                        'photo_path' => $user->profile_photo ?? null,
                    ]
                );
            }
            $this->command->info("✅ Berhasil membuat {$authorUsers->count()} authors dari existing users.");
        } else {
            $this->command->warn('⚠️  Tidak ada user dengan role "author" ditemukan.');
        }

        // ✅ MANUAL CREATE tanpa factory (jika factory error)
        $this->command->info('🔄 Membuat 20 external authors...');

        $universities = [
            'Universitas Gadjah Mada',
            'Institut Teknologi Bandung',
            'Universitas Indonesia',
            'Institut Pertanian Bogor',
            'Universitas Airlangga',
            'Universitas Brawijaya',
            'Universitas Diponegoro',
            'Institut Teknologi Sepuluh Nopember',
        ];

        for ($i = 1; $i <= 20; $i++) {
            Author::create([
                'user_id' => null,
                'name' => "External Author {$i}",
                'email' => "external.author{$i}@example.com",
                'affiliation' => $universities[array_rand($universities)],
                'bio' => "Bio for External Author {$i}. Experienced researcher with expertise in various scientific fields.",
                'photo_path' => null,
            ]);
        }
        $this->command->info('✅ Berhasil membuat 20 external authors.');

        // ✅ MANUAL CREATE dengan user baru
        $this->command->info('🔄 Membuat 10 authors dengan user account baru...');

        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => "Author User {$i}",
                'email' => "author.user{$i}@example.com",
                'password' => bcrypt('password'),
            ]);

            if (method_exists($user, 'assignRole')) {
                $user->assignRole('author');
            }

            Author::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'affiliation' => $universities[array_rand($universities)],
                'bio' => "Bio for {$user->name}. Experienced researcher with expertise in various scientific fields.",
                'photo_path' => null,
            ]);
        }
        $this->command->info('✅ Berhasil membuat 10 authors dengan user account.');

        // Tampilkan summary
        $totalAuthors = Author::count();
        $authorsWithUser = Author::whereNotNull('user_id')->count();
        $externalAuthors = Author::whereNull('user_id')->count();

        $this->command->info("\n📊 SUMMARY:");
        $this->command->info("Total Authors: {$totalAuthors}");
        $this->command->info("Authors dengan User Account: {$authorsWithUser}");
        $this->command->info("External Authors: {$externalAuthors}");
    }
}

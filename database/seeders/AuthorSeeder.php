<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
                        'photo_path' => $user->profile_photo ?? null, // ✅ Ambil dari user profile_photo
                    ]
                );
            }
            $this->command->info("✅ Berhasil membuat {$authorUsers->count()} authors dari existing users.");
        } else {
            $this->command->warn('⚠️  Tidak ada user dengan role "author" ditemukan.');
        }

        // Buat additional authors tanpa user account (external authors)
        $this->command->info('🔄 Membuat 20 external authors...');
        Author::factory()->count(20)->create();
        $this->command->info('✅ Berhasil membuat 20 external authors.');

        // Buat beberapa authors dengan user account baru
        $this->command->info('🔄 Membuat 10 authors dengan user account baru...');
        Author::factory()
            ->withUser()
            ->count(10)
            ->create();
        $this->command->info('✅ Berhasil membuat 10 authors dengan user account.');

        // ✅ Tampilkan summary
        $totalAuthors = Author::count();
        $authorsWithUser = Author::whereNotNull('user_id')->count();
        $externalAuthors = Author::whereNull('user_id')->count();

        $this->command->info("\n📊 SUMMARY:");
        $this->command->info("Total Authors: {$totalAuthors}");
        $this->command->info("Authors dengan User Account: {$authorsWithUser}");
        $this->command->info("External Authors: {$externalAuthors}");
    }
}

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
        // Buat authors dari existing users dengan role 'author'
        $authorUsers = User::role('author')->get();

        foreach ($authorUsers as $user) {
            Author::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'affiliation' => $user->job_title ?? 'Independent Researcher',
                    'bio' => "Bio for {$user->name}. Experienced researcher with expertise in various scientific fields.",
                    'photo_path' => null,
                ]
            );
        }

        // Buat additional authors tanpa user account (external authors)
        Author::factory()->count(20)->create();

        // Buat beberapa authors dengan user account baru
        Author::factory()
            ->withUser()
            ->count(10)
            ->create();
    }
}

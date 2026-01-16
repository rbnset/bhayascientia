<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    public function run(): void
    {
        Author::updateOrCreate(
            ['email' => 'super_admin@admin.com'],
            [
                'user_id' => 1,
                'name' => 'Super Admin',
                'affiliation' => 'BHAYASCIENTIA',
                'bio' => 'Experienced researcher with expertise in scientific publications and academic writing.',
                'photo_path' => null, // Akan menggunakan default avatar
            ]
        );

        Author::updateOrCreate(
            ['email' => 'dorman@gmail.com'],
            [
                'user_id' => 1,
                'name' => 'Dorman',
                'affiliation' => 'UPN',
                'bio' => 'Academic researcher specializing in various fields of study.',
                'photo_path' => null,
            ]
        );
    }
}

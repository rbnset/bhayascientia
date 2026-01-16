<?php

namespace Database\Seeders;

use App\Models\PublicationType;
use Illuminate\Database\Seeder;

class PublicationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'slug' => 'book',
                'name' => 'Book',
                'description' => 'Karya berbentuk buku utuh (authorship sebuah buku)',
                'requires_review' => false,
                'is_active' => true,
            ],
            [
                'slug' => 'journal-article',
                'name' => 'Journal Article',
                'description' => 'Artikel jurnal ilmiah',
                'requires_review' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'conference-paper',
                'name' => 'Conference Paper',
                'description' => 'Paper untuk konferensi',
                'requires_review' => true,
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            PublicationType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}

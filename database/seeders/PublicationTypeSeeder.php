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
                'slug' => 'buku',
                'name' => 'Buku',
                'description' => 'Karya berbentuk buku utuh (authorship sebuah buku)',
                'requires_review' => false,
                'is_active' => true,
            ],
            [
                'slug' => 'jurnal',
                'name' => 'Jurnal',
                'description' => 'Artikel jurnal ilmiah yang dipublikasikan dalam jurnal peer-reviewed',
                'requires_review' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'opini',
                'name' => 'Opini',
                'description' => 'Artikel opini berbasis fakta yang menyajikan pandangan ahli terhadap isu terkini',
                'requires_review' => false,
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            PublicationType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }

        $this->command->info('Berhasil membuat ' . count($types) . ' tipe publikasi.');
    }
}

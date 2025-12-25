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
                'slug' => 'journal-article',
                'name' => 'Journal Article',
                'description' => 'Artikel ilmiah yang dipublikasikan di jurnal (umumnya melalui proses penelaahan/peer review).',
                'requires_review' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'review-article',
                'name' => 'Review Article',
                'description' => 'Artikel tinjauan yang merangkum dan menganalisis literatur yang sudah ada (mis. systematic/scoping/narrative review).',
                'requires_review' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'conference-paper-proceedings',
                'name' => 'Conference Paper (Proceedings)',
                'description' => 'Paper yang dipresentasikan di konferensi dan diterbitkan dalam prosiding (full paper atau ringkasan).',
                'requires_review' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'book',
                'name' => 'Book',
                'description' => 'Karya berbentuk buku utuh (authorship sebuah buku).',
                'requires_review' => false,
                'is_active' => true,
            ],
            [
                'slug' => 'book-chapter',
                'name' => 'Book Chapter',
                'description' => 'Kontribusi satu atau lebih bab dalam buku/edited volume.',
                'requires_review' => false,
                'is_active' => true,
            ],
            [
                'slug' => 'editorial',
                'name' => 'Editorial',
                'description' => 'Tulisan yang menyampaikan opini/kebijakan editor/penerbit jurnal terkait isu atau topik terkini.',
                'requires_review' => false,
                'is_active' => true,
            ],
            [
                'slug' => 'opinion-commentary',
                'name' => 'Opinion / Commentary',
                'description' => 'Tulisan opini/komentar yang membahas, mendukung, atau mengkritisi karya/pembahasan yang sudah dipublikasikan.',
                'requires_review' => false,
                'is_active' => true,
            ],
            [
                'slug' => 'letter-to-editor',
                'name' => 'Letter to the Editor',
                'description' => 'Surat/korespondensi ilmiah kepada editor, biasanya menanggapi artikel atau isu tertentu.',
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
    }
}

<?php

namespace Database\Seeders;

use App\Models\Method;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            // Pendekatan Utama
            'Kuantitatif',
            'Kualitatif',
            'Mixed Methods',
            'literatur-review',
            'simulasi-komputasi',

            // Desain Kuantitatif
            'Eksperimental',
            'Quasi-Eksperimental',
            'Survei',
            'Korelasional',
            'Cross-Sectional',
            'Longitudinal',

            // Desain Kualitatif
            'Studi Kasus',
            'Fenomenologi',
            'Etnografi',
            'Grounded Theory',
            'Focus Group Discussion (FGD)',
            'In-depth Interview',

            // Pendekatan Lainnya
            'Systematic Literature Review',
            'Meta-Analysis',
            'Action Research',
        ];

        foreach ($methods as $name) {
            Method::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }

        $this->command->info('Berhasil membuat ' . count($methods) . ' metode penelitian.');
    }
}

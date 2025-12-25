<?php

namespace Database\Seeders;

use App\Models\Method;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MethodSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            // Payung besar
            'Quantitative',
            'Qualitative',
            'Mixed Methods',

            // Kuantitatif (desain umum)
            'Experimental',
            'Quasi-Experimental',
            'Survey',
            'Correlational',
            'Cross-Sectional',
            'Longitudinal',

            // Kualitatif (desain umum)
            'Case Study',
            'Phenomenology',
            'Ethnography',
            'Grounded Theory',
            'Focus Group Discussion (FGD)',
            'In-depth Interview',

            // Pendekatan lain yang sering dipakai
            'Systematic Literature Review',
            'Meta-Analysis',
            'Action Research',
        ];

        foreach ($names as $name) {
            Method::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}

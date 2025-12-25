<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            // Forensik / Keamanan (crime-related)
            'Crime & Bombing Cases',
            'Terrorism & Security',
            'Post-blast Investigation',
            'Improvised Explosive Devices (IED)',
            'Explosives Forensics (Trace Analysis)',
            'Explosives Finds & Caches',

            // Sains/teknik ledakan
            'Explosives Chemistry & Materials',
            'Detonation & Blast Physics',
            'Shock Waves & Overpressure Modeling',
            'Fragmentation & Shrapnel',
            'Blast Effects on Structures',
            'Explosion Safety & Risk Assessment',

            // Industri/pertambangan
            'Mining & Quarry Blasting',
            'Demolition & Controlled Blasting',
            'Industrial Accidents (Process Safety)',

            // Jenis kejadian eksplosi
            'Dust Explosions',
            'Vapour Cloud Explosions (VCE)',
            'BLEVE',
            'Fireworks Misuse & Regulation',
        ];

        foreach ($names as $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}

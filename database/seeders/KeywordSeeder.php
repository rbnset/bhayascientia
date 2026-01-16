<?php

namespace Database\Seeders;

use App\Models\Keyword;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KeywordSeeder extends Seeder
{
    public function run(): void
    {
        $keywords = [
            // Umum ledakan / blast
            'Explosion',
            'Blast wave',
            'Overpressure',
            'Impulse',
            'Shock wave',
            'Blast loading',
            'Blast mitigation',
            'Structural response',
            'Dynamic response',
            'Fragmentation',
            'Shrapnel',

            // Forensik / keamanan
            'Explosive residue',
            'Post-blast investigation',
            'Bombing',
            'Crime scene',
            'Forensic analysis',
            'Trace evidence',
            'Improvised explosive device (IED)',
            'Terrorism',

            // Industri / pertambangan (blasting)
            'Mining blasting',
            'Drilling and blasting',
            'Blast design',
            'Ground vibration',
            'Peak particle velocity (PPV)',
            'Airblast',
            'Flyrock',
            'Delay timing',
            'Electronic detonator',
            'Rock fragmentation',
            'Blasting optimization',
            'Scaled distance',

            // Material ledakan
            'TNT',
            'C4',
            'ANFO',
            'Dynamite',
            'Explosive composition',
            'Detonation velocity',
            'Detonation pressure',

            // Safety & Risk
            'Blast safety',
            'Risk assessment',
            'Safety distance',
            'Protective structure',
        ];

        foreach ($keywords as $name) {
            Keyword::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }

        $this->command->info('Berhasil membuat ' . count($keywords) . ' keywords.');
    }
}

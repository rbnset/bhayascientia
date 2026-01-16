<?php

namespace App\Helpers;

class StatusHelper
{
    /**
     * Daftar status publikasi dengan styling
     */
    public static function getStatusConfig(string $status): array
    {
        $configs = [
            'Terverifikasi' => [
                'label' => 'Terverifikasi',
                'icon' => 'check-badge',
                'color' => 'emerald',
                'bg' => 'bg-emerald-50',
                'text' => 'text-emerald-700',
                'ring' => 'ring-emerald-200',
            ],
            'Peer-reviewed' => [
                'label' => 'Peer-reviewed',
                'icon' => 'academic-cap',
                'color' => 'blue',
                'bg' => 'bg-blue-50',
                'text' => 'text-blue-700',
                'ring' => 'ring-blue-200',
            ],
            'Pilihan Editor' => [
                'label' => 'Pilihan Editor',
                'icon' => 'star',
                'color' => 'amber',
                'bg' => 'bg-amber-50',
                'text' => 'text-amber-700',
                'ring' => 'ring-amber-200',
            ],
            'Banyak Dikutip' => [
                'label' => 'Banyak Dikutip',
                'icon' => 'fire',
                'color' => 'orange',
                'bg' => 'bg-orange-50',
                'text' => 'text-orange-700',
                'ring' => 'ring-orange-200',
            ],
        ];

        return $configs[$status] ?? $configs['Terverifikasi'];
    }
}

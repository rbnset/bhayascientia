<?php
// config/publication.php

return [
    /**
     * ✅ UNIFIED NAVIGATION - Single source untuk desktop & mobile
     */
    'navigation' => [
        [
            'label' => 'Browse',
            'href' => 'publikasi.index',
            'active' => ['publikasi.index', 'publikasi.show'],
            'icon' => 'assets/images/icons/3dcube.svg',
            'iconWhite' => 'assets/images/icons/3dcube-white.svg', // Untuk mobile inactive
            'badge' => null,
            'new' => false,
        ],
        [
            'label' => 'Categories',
            'href' => 'publikasi.categories',
            'active' => ['publikasi.categories'],
            'icon' => 'assets/images/icons/calendar-date-range-dark.svg',
            'iconWhite' => 'assets/images/icons/calendar-date-range-white.svg',
            'badge' => null,
            'new' => false,
        ],
        [
            'label' => 'Trending',
            'href' => 'publikasi.trending',
            'active' => ['publikasi.trending'],
            'icon' => 'assets/images/icons/star-dark.svg',
            'iconWhite' => 'assets/images/icons/star-white.svg',
            'badge' => null,
            'new' => true,
        ],
        [
            'label' => 'My Library',
            'href' => 'publikasi.library',
            'active' => ['publikasi.library'],
            'icon' => 'assets/images/icons/star-dark.svg',
            'iconWhite' => 'assets/images/icons/star-white.svg',
            'badge' => fn() => auth()->check() ? auth()->user()->savedPublications()->count() : 0,
            'new' => false,
            'auth' => false, // Hanya tampil jika login
        ],
    ],

    /**
     * Publication Types (untuk filter)
     */
    'publication_types' => [
        'all' => 'Semua',
        'book' => 'Buku',
        'journal' => 'Jurnal',
        'opinion' => 'Opini',
    ],
];

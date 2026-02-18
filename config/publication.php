<?php
// config/publication.php

return [
    /**
     * ✅ UNIFIED NAVIGATION - Single source untuk desktop & mobile
     */
    'navigation' => [
        [
            'label'     => 'Browse',
            'href'      => 'publikasi.browse',
            'active'    => ['publikasi.browse', 'publikasi.index'],
            'icon'      => 'assets/images/icons/3dcube.svg',
            'iconWhite' => 'assets/images/icons/3dcube-white.svg',
            'badge'     => null,
            'new'       => false,
        ],
        [
            'label'     => 'Categories',
            'href'      => 'publikasi.category',           // ✅ diperbaiki dari 'publikasi.categories'
            'active'    => ['publikasi.category', 'publikasi.category.show'],  // ✅ include show
            'icon'      => 'assets/images/icons/calendar-date-range-dark.svg',
            'iconWhite' => 'assets/images/icons/calendar-date-range-white.svg',
            'badge'     => null,
            'new'       => false,
        ],
        [
            'label'     => 'Trending',
            'href'      => 'publikasi.trending',
            'active'    => ['publikasi.trending'],
            'icon'      => 'assets/images/icons/star-dark.svg',
            'iconWhite' => 'assets/images/icons/star-white.svg',
            'badge'     => null,
            'new'       => true,
        ],
        [
            'label'     => 'My Library',
            'href'      => 'publikasi.library',
            'active'    => ['publikasi.library'],
            'icon'      => 'assets/images/icons/star-dark.svg',
            'iconWhite' => 'assets/images/icons/star-white.svg',
            'badge'     => fn() => auth()->check()
                ? auth()->user()->savedPublications()->count()
                : 0,
            'new'       => false,
            'auth'      => false, // ✅ tampil selalu, tapi konten locked jika belum login
        ],
    ],

    /**
     * Publication Types (untuk filter)
     */
    'publication_types' => [
        'all'     => 'Semua',
        'book'    => 'Buku',
        'journal' => 'Jurnal',
        'opinion' => 'Opini',
    ],
];

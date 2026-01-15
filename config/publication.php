<?php
// config/publication.php

return [
    'sub_navigation' => [
        [
            'label' => 'Browse',
            'icon' => 'assets/images/icons/3dcube.svg',
            'href' => 'publikasi.index',
            'active' => ['publikasi.index', 'publikasi.show'], // Hapus 'publikasi'
        ],
        [
            'label' => 'Categories',
            'icon' => 'assets/images/icons/calendar-date-range-dark.svg',
            'href' => 'publikasi.categories',
            'active' => ['publikasi.categories'],
        ],
        [
            'label' => 'Trending',
            'icon' => 'assets/images/icons/user-dark.svg',
            'href' => 'publikasi.trending',
            'active' => ['publikasi.trending'],
            'new' => true,
        ],
        [
            'label' => 'My Library',
            'icon' => 'assets/images/icons/star-dark.svg',
            'href' => 'publikasi.library',
            'active' => ['publikasi.library'],
            'badge' => fn() => auth()->check() ? 24 : 0,
        ],
    ],

    'bottom_navigation' => [
        [
            'label' => 'Browse',
            'href' => 'publikasi.index',
            'active' => ['publikasi.index', 'publikasi.show'], // Hapus 'publikasi'
            'icon' => 'assets/images/icons/3dcube-white.svg',
            'iconActive' => 'assets/images/icons/3dcube.svg',
        ],
        [
            'label' => 'Categories',
            'href' => 'publikasi.categories',
            'active' => ['publikasi.categories'],
            'icon' => 'assets/images/icons/grid-white.svg',
            'iconActive' => 'assets/images/icons/grid-dark.svg',
        ],
        [
            'label' => 'Trending',
            'href' => 'publikasi.trending',
            'active' => ['publikasi.trending'],
            'icon' => 'assets/images/icons/fire-white.svg',
            'iconActive' => 'assets/images/icons/fire-dark.svg',
            'new' => true,
        ],
        [
            'label' => 'Library',
            'href' => 'publikasi.library',
            'active' => ['publikasi.library'],
            'icon' => 'assets/images/icons/book-white.svg',
            'iconActive' => 'assets/images/icons/book-dark.svg',
            'badge' => fn() => auth()->check() ? 24 : 0,
        ],
    ],

    'publication_types' => [
        'all' => 'Semua',
        'book' => 'Buku',
        'journal' => 'Jurnal',
        'opinion' => 'Opini',
    ],
];

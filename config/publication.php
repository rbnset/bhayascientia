<?php
// config/publication.php

return [
    'navigation' => [
        [
            'label'     => 'Browse',
            'href'      => 'publikasi.index',
            'active'    => ['publikasi.index', 'publikasi.show', 'publikasi.browse'],
            'icon'      => 'assets/images/icons/browse-dark.svg',
            'iconWhite' => 'assets/images/icons/browse-white.svg',
            'badge'     => null,
            'new'       => false,
        ],
        [
            'label'     => 'Categories',
            'href'      => 'publikasi.category',
            'active'    => ['publikasi.category', 'publikasi.category.show'],
            'icon'      => 'assets/images/icons/category-dark.svg',
            'iconWhite' => 'assets/images/icons/category-white.svg',
            'badge'     => null,
            'new'       => false,
        ],
        [
            'label'     => 'Trending',
            'href'      => 'publikasi.trending',
            'active'    => ['publikasi.trending'],
            'icon'      => 'assets/images/icons/trending-dark.svg',
            'iconWhite' => 'assets/images/icons/trending-white.svg',
            'badge'     => null,
            'new'       => true,
        ],
        [
            'label'     => 'My Library',
            'href'      => 'publikasi.library',
            'active'    => ['publikasi.library'],
            'icon'      => 'assets/images/icons/library-dark.svg',
            'iconWhite' => 'assets/images/icons/library-white.svg',
            'badge'     => null, // ← dihitung dinamis via View Composer
            'new'       => false,
            'auth'      => false,
        ],
    ],

    'publication_types' => [
        'all'     => 'Semua',
        'book'    => 'Buku',
        'journal' => 'Jurnal',
        'opinion' => 'Opini',
    ],
];

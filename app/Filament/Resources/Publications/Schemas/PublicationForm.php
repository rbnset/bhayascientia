<?php

namespace App\Filament\Resources\Publications\Schemas;

use App\Models\Author;
use App\Models\Category;
use App\Models\Keyword;
use App\Models\Method;
use App\Models\PublicationType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PublicationForm
{
    /**
     * Ambil slug publication type secara aman
     */
    private static function publicationTypeSlug(callable $get): ?string
    {
        $id = $get('publication_type_id');

        if (! $id) {
            return null;
        }

        return PublicationType::query()
            ->whereKey($id)
            ->value('slug');
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                // =========================
                // BASIC INFORMATION
                // =========================
                Section::make('Publication Information')
                    ->description('Informasi utama karya ilmiah')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Select::make('publication_type_id')
                            ->label('Publication Type')
                            ->relationship(
                                name: 'publicationType',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) =>
                                $query->where('is_active', true)
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn($set) => $set('abstract', null)),

                        TextInput::make('title')
                            ->label(fn($get) => match (self::publicationTypeSlug($get)) {
                                'jurnal' => 'Judul Artikel Jurnal',
                                'buku'   => 'Judul Buku',
                                'opini'  => 'Judul Opini',
                                default  => 'Judul Publikasi',
                            })
                            ->required()
                            ->maxLength(255),

                        Textarea::make('abstract')
                            ->rows(6)
                            ->columnSpanFull()
                            ->label(fn($get) => match (self::publicationTypeSlug($get)) {
                                'jurnal' => 'Abstrak',
                                'buku'   => 'Ringkasan',
                                default  => 'Ringkasan',
                            })
                            ->visible(
                                fn($get) =>
                                self::publicationTypeSlug($get) !== 'opini'
                            )
                            ->required(
                                fn($get) =>
                                self::publicationTypeSlug($get) === 'jurnal'
                            )
                            ->helperText(fn($get) => match (self::publicationTypeSlug($get)) {
                                'jurnal' => 'Abstrak wajib sesuai standar artikel jurnal ilmiah',
                                'buku'   => 'Ringkasan isi buku (opsional)',
                                default  => 'Ringkasan singkat (opsional)',
                            }),

                        /**
                         * =====================
                         * KEYWORDS (JURNAL ONLY)
                         * =====================
                         */
                        Select::make('keywords')
                            ->label('Keywords')
                            ->relationship(
                                name: 'keywords',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) =>
                                $query->orderBy('name')
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()

                            // 🔥 HANYA MUNCUL JIKA JURNAL
                            ->visible(
                                fn($get) =>
                                self::publicationTypeSlug($get) === 'jurnal'
                            )

                            // 🔴 WAJIB JIKA JURNAL
                            ->required(
                                fn($get) =>
                                self::publicationTypeSlug($get) === 'jurnal'
                            )

                            // ✨ BUAT KEYWORD BARU LANGSUNG
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Keyword')
                                    ->required()
                                    ->maxLength(100)
                                    ->live(onBlur: true)
                                    ->unique(
                                        table: 'keywords',
                                        column: 'name',
                                        ignoreRecord: true
                                    )
                                    ->afterStateUpdated(
                                        fn($state, callable $set) =>
                                        $set('slug', Str::slug($state))
                                    ),

                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->createOptionUsing(
                                fn(array $data) =>
                                Keyword::create($data)->getKey()
                            )
                            ->helperText('Wajib untuk jurnal, pisahkan konsep utama penelitian'),

                    ]),

                // =========================
                // CLASSIFICATION
                // =========================
                Section::make('Classification')
                    ->description('Kategori dan metode penelitian')
                    ->icon('heroicon-o-tag')
                    ->schema([

                        /**
                         * =====================
                         * CATEGORIES (M2M)
                         * =====================
                         */
                        Select::make('categories')
                            ->label('Categories')
                            ->relationship(
                                name: 'categories',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) =>
                                $query->orderBy('name')
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()

                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Category Name')
                                    ->required()
                                    ->maxLength(100)
                                    ->live(onBlur: true)
                                    ->unique(
                                        table: 'categories',
                                        column: 'name',
                                        ignoreRecord: true
                                    )
                                    ->afterStateUpdated(
                                        fn($state, callable $set) =>
                                        $set('slug', Str::slug($state))
                                    ),

                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->createOptionUsing(
                                fn(array $data) =>
                                Category::create($data)->getKey()
                            ),

                        /**
                         * =====================
                         * RESEARCH METHOD (BELONGS TO)
                         * =====================
                         */
                        Select::make('method_id')
                            ->label('Research Method')
                            ->relationship(
                                name: 'method',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) =>
                                $query->orderBy('name')
                            )
                            ->searchable()
                            ->preload()

                            // 🔥 CREATE METHOD FROM HERE
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Method Name')
                                    ->required()
                                    ->maxLength(100)
                                    ->live(onBlur: true)
                                    ->unique(
                                        table: 'methods',
                                        column: 'name',
                                        ignoreRecord: true
                                    )
                                    ->afterStateUpdated(
                                        fn($state, callable $set) =>
                                        $set('slug', Str::slug($state))
                                    ),

                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->createOptionUsing(
                                fn(array $data) =>
                                Method::create($data)->getKey()
                            ),
                    ]),

                // =========================
                // MEDIA
                // =========================
                Section::make('Cover & Files')
                    ->description('Media pendukung publikasi')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        FileUpload::make('cover_image_path')
                            ->label('Cover Image')
                            ->image()
                            ->directory('publications/covers')
                            ->imagePreviewHeight('200')
                            ->maxSize(2048),
                    ]),


                // =========================
                // AUTHOR
                // =========================
                Section::make('Authors')
                    ->description('Tambah penulis dan atur urutan dengan drag & drop')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Repeater::make('authorPublications')
                            ->label('Authors')
                            ->relationship('authorPublications')
                            // Ini yang bikin urutan tersimpan saat drag & drop (ke kolom "order"). [web:119]
                            ->orderColumn('order')
                            ->reorderable() // drag & drop handle muncul
                            ->collapsed()   // biar ringkas kalau author banyak (opsional)
                            ->defaultItems(1)
                            ->schema([
                                Select::make('author_id')
                                    ->label('Author')
                                    ->relationship('author', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    // UX: bikin author baru langsung dari sini (tanpa ID)
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Name')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(table: 'authors', column: 'email', ignoreRecord: true),

                                        TextInput::make('affiliation')
                                            ->label('Affiliation')
                                            ->maxLength(255)
                                            ->nullable(),
                                    ])
                                    ->createOptionUsing(fn(array $data) => Author::create($data)->getKey()),

                                // Tidak ada input "order" (angka) sama sekali.
                                // "Corresponding author" juga tidak ditampilkan, karena akan di-set otomatis.
                            ])
                            // Set corresponding otomatis: author yang user login (yang upload)
                            ->mutateDehydratedStateUsing(function (array $state): array {
                                $authorId = \App\Models\Author::query()
                                    ->where('user_id', auth()->id())
                                    ->value('id');

                                // Kalau user login belum punya Author record, ya tidak ada yang bisa di-set.
                                if (! $authorId) {
                                    return $state;
                                }

                                foreach ($state as $i => $item) {
                                    $state[$i]['is_corresponding'] = ((int) ($item['author_id'] ?? 0) === (int) $authorId);
                                }

                                return $state;
                            }),
                    ])
                    ->columnSpanFull(),

                // =========================
                // PUBLICATION STATUS
                // =========================
                Section::make('Publication Status')
                    ->description('Status proses publikasi')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Submitted',
                                'in_review' => 'In Review',
                                'revision_required' => 'Revision Required',
                                'accepted' => 'Accepted',
                                'rejected' => 'Rejected',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->required(),

                        DateTimePicker::make('published_at')
                            ->label('Published At')
                            ->visible(
                                fn($get) =>
                                $get('status') === 'published'
                            ),
                    ]),
            ]);
    }
}

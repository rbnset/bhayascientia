<?php

namespace App\Filament\Resources\Publications\Schemas;

use App\Models\Author;
use App\Models\Category;
use App\Models\Keyword;
use App\Models\Method;
use App\Models\PublicationType;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Utilities\Set;

class PublicationForm
{
    private static function isReviewer(): bool
    {
        return (bool) auth()->user()?->hasRole('reviewer');
    }

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
                Wizard::make([

                    // ─────────────────────────────────────────
                    // STEP 1 — Informasi Publikasi
                    // ─────────────────────────────────────────
                    Step::make('Informasi Publikasi')
                        ->description('Tipe, judul, ringkasan, dan kata kunci')
                        ->icon('heroicon-o-document-text')
                        ->completedIcon('heroicon-o-check-circle')
                        ->columns(2)
                        ->schema([
                            Section::make('Publication Information')
                                ->description('Informasi utama karya ilmiah')
                                ->icon('heroicon-o-document-text')
                                ->columnSpanFull()
                                ->schema([

                                    // ── Publication Type ──────────────────────────
                                    Select::make('publication_type_id')
                                        ->label('Publication Type')
                                        ->relationship(
                                            name: 'publicationType',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn($query) => $query->where('is_active', true),
                                        )
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(fn($set) => $set('abstract', null))
                                        ->helperText('Pilih tipe publikasi terlebih dahulu agar field lain menyesuaikan.')
                                        ->disabled(fn() => self::isReviewer())
                                        ->columnSpan(1),

                                    // ── Title ─────────────────────────────────────
                                    \Filament\Forms\Components\TextInput::make('title')
                                        ->label(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Judul Artikel',
                                            'buku'   => 'Judul Buku',
                                            'opini'  => 'Judul Opini',
                                            default  => 'Judul Publikasi',
                                        })
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Contoh: Pengaruh X terhadap Y pada Konteks Z',
                                            'buku'   => 'Contoh: Panduan Lengkap Sistem Informasi',
                                            'opini'  => 'Contoh: Mengapa Digitalisasi Desa Masih Lambat?',
                                            default  => 'Tulis judul yang jelas dan ringkas.',
                                        })
                                        ->disabled(fn() => self::isReviewer())
                                        ->columnSpanFull(),

                                    // ── Abstract: berbeda label & helper per tipe ─
                                    // Jurnal → "Abstrak" (wajib, formal)
                                    RichEditor::make('abstract')
                                        ->columnSpanFull()
                                        ->label('Abstrak')
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->required(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->toolbarButtons([
                                            ['bold', 'italic', 'underline', 'strike', 'link'],
                                            ['bulletList', 'orderedList', 'blockquote'],
                                            ['undo', 'redo'],
                                        ])
                                        ->helperText('Wajib. Tulis abstrak sesuai standar jurnal (latar belakang, tujuan, metode, hasil, simpulan).')
                                        ->disabled(fn() => self::isReviewer()),

                                    // Buku → "Sinopsis" (opsional, naratif)
                                    RichEditor::make('abstract')
                                        ->columnSpanFull()
                                        ->label('Sinopsis')
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'buku')
                                        ->required(false)
                                        ->toolbarButtons([
                                            ['bold', 'italic', 'underline', 'link'],
                                            ['bulletList', 'orderedList'],
                                            ['undo', 'redo'],
                                        ])
                                        ->helperText('Opsional. Tulis sinopsis menarik yang membuat pembaca ingin membaca buku ini.')
                                        ->disabled(fn() => self::isReviewer()),

                                    // Opini → "Isi Opini" (wajib, konten utama)
                                    RichEditor::make('abstract')
                                        ->columnSpanFull()
                                        ->label('Isi Opini')
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'opini')
                                        ->required(fn($get) => self::publicationTypeSlug($get) === 'opini')
                                        ->toolbarButtons([
                                            ['bold', 'italic', 'underline', 'strike', 'link'],
                                            ['bulletList', 'orderedList', 'blockquote', 'h2', 'h3'],
                                            ['undo', 'redo'],
                                        ])
                                        ->helperText('Wajib. Tulis isi opini secara lengkap dan argumentatif.')
                                        ->disabled(fn() => self::isReviewer()),

                                    // ── Keywords (Jurnal) — label "Keywords" ──────
                                    Select::make('keywords')
                                        ->label('Keywords')
                                        ->relationship(
                                            name: 'keywords',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn($query) => $query->orderBy('name'),
                                        )
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->required(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->createOptionForm([
                                            \Filament\Forms\Components\TextInput::make('name')
                                                ->label('Keyword')
                                                ->required()
                                                ->maxLength(100)
                                                ->live(onBlur: true)
                                                ->unique(table: 'keywords', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                            \Filament\Forms\Components\TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->disabled()
                                                ->dehydrated(),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Keyword::create($data)->getKey())
                                        ->helperText('Wajib. Pilih 3–7 keyword yang mewakili konsep utama penelitian.')
                                        ->disabled(fn() => self::isReviewer())
                                        ->columnSpanFull(),

                                    // ── Tags (Buku) — label "Tags" ────────────────
                                    Select::make('keywords')
                                        ->label('Tags')
                                        ->relationship(
                                            name: 'keywords',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn($query) => $query->orderBy('name'),
                                        )
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'buku')
                                        ->required(false)
                                        ->createOptionForm([
                                            \Filament\Forms\Components\TextInput::make('name')
                                                ->label('Tag')
                                                ->required()
                                                ->maxLength(100)
                                                ->live(onBlur: true)
                                                ->unique(table: 'keywords', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                            \Filament\Forms\Components\TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->disabled()
                                                ->dehydrated(),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Keyword::create($data)->getKey())
                                        ->helperText('Opsional. Tambahkan tag yang membantu pembaca menemukan buku ini.')
                                        ->disabled(fn() => self::isReviewer())
                                        ->columnSpanFull(),

                                    // ── Topik (Opini) — label "Topik" ─────────────
                                    Select::make('keywords')
                                        ->label('Topik')
                                        ->relationship(
                                            name: 'keywords',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn($query) => $query->orderBy('name'),
                                        )
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'opini')
                                        ->required(false)
                                        ->maxItems(3)
                                        ->createOptionForm([
                                            \Filament\Forms\Components\TextInput::make('name')
                                                ->label('Topik')
                                                ->required()
                                                ->maxLength(100)
                                                ->live(onBlur: true)
                                                ->unique(table: 'keywords', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                            \Filament\Forms\Components\TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->disabled()
                                                ->dehydrated(),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Keyword::create($data)->getKey())
                                        ->helperText('Opsional. Maks. 3 topik utama yang dibahas dalam opini ini.')
                                        ->disabled(fn() => self::isReviewer())
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // ─────────────────────────────────────────
                    // STEP 2 — Klasifikasi
                    // ─────────────────────────────────────────
                    // ─────────────────────────────────────────
                    // STEP 2 — Klasifikasi
                    // ─────────────────────────────────────────
                    Step::make('Klasifikasi')
                        ->description('Kategori & metode penelitian')
                        ->icon('heroicon-o-tag')
                        ->completedIcon('heroicon-o-check-circle')
                        ->columns(2)
                        ->schema([
                            Section::make('Classification')
                                ->description('Kategori dan metode penelitian')
                                ->icon('heroicon-o-tag')
                                ->columnSpanFull()
                                ->schema([

                                    // ── Category (hanya 1, semua tipe wajib) ──────
                                    Select::make('categories')
                                        ->label('Category')
                                        ->relationship(
                                            name: 'categories',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn($query) => $query->orderBy('name'),
                                        )
                                        ->multiple()        // ← wajib karena relasi BelongsToMany
                                        ->maxItems(1)       // ← batasi hanya 1 pilihan
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->createOptionForm([
                                            \Filament\Forms\Components\TextInput::make('name')
                                                ->label('Category Name')
                                                ->required()
                                                ->maxLength(100)
                                                ->live(onBlur: true)
                                                ->unique(table: 'categories', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                            \Filament\Forms\Components\TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->disabled()
                                                ->dehydrated(),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Category::create($data)->getKey())
                                        ->helperText('Pilih 1 kategori yang paling mewakili publikasi ini.')
                                        ->disabled(fn() => self::isReviewer())
                                        ->columnSpan(1),


                                    // ── Research Method ───────────────────────────
                                    // Jurnal  → wajib
                                    // Buku    → opsional (tampil)
                                    // Opini   → disembunyikan
                                    Select::make('method_id')
                                        ->label(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Research Method',
                                            'buku'   => 'Metode Penulisan',
                                            default  => 'Research Method',
                                        })
                                        ->relationship(
                                            name: 'method',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn($query) => $query->orderBy('name'),
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn($get) => self::publicationTypeSlug($get) !== 'opini')
                                        ->required(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->createOptionForm([
                                            \Filament\Forms\Components\TextInput::make('name')
                                                ->label('Method Name')
                                                ->required()
                                                ->maxLength(100)
                                                ->live(onBlur: true)
                                                ->unique(table: 'methods', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                            \Filament\Forms\Components\TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->disabled()
                                                ->dehydrated(),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Method::create($data)->getKey())
                                        ->helperText(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Wajib. Pilih metode penelitian utama yang digunakan.',
                                            'buku'   => 'Opsional. Pilih jika buku menggunakan pendekatan metodologi tertentu.',
                                            default  => '',
                                        })
                                        ->disabled(fn() => self::isReviewer())
                                        ->columnSpan(1),

                                    // ── Info hint untuk opini (pengganti method) ──
                                    \Filament\Forms\Components\Placeholder::make('method_info')
                                        ->label('')
                                        ->content('Opini tidak memerlukan klasifikasi metode penelitian.')
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'opini')
                                        ->columnSpan(1),
                                ]),
                        ]),

                    // ─────────────────────────────────────────
                    // STEP 3 — Penulis
                    // ─────────────────────────────────────────
                    Step::make('Penulis')
                        ->description('Penulis utama & tambahan')
                        ->icon('heroicon-o-users')
                        ->completedIcon('heroicon-o-check-circle')
                        ->schema([
                            Section::make('Authors')
                                ->description('Penulis corresponding mengikuti data publication (tidak ikut user yang sedang login)')
                                ->icon('heroicon-o-users')
                                ->schema([
                                    Repeater::make('authorPublications')
                                        ->label('Authors')
                                        ->deletable(false)
                                        ->relationship('authorPublications')
                                        ->orderColumn('order')
                                        ->reorderable()
                                        ->defaultItems(0)
                                        ->minItems(1)
                                        ->addActionLabel('Tambah penulis')
                                        ->collapsed()
                                        ->afterStateHydrated(function (?array $state, callable $set) {
                                            $state ??= [];
                                            $state = array_values($state);

                                            if (count($state) > 0) {
                                                foreach ($state as $i => $row) {
                                                    $state[$i]['order'] = $i + 1;
                                                }
                                                $set('authorPublications', $state);
                                                return;
                                            }

                                            $currentUser = auth()->user();
                                            if (! $currentUser) {
                                                return;
                                            }

                                            $currentAuthor = Author::query()->firstOrCreate(
                                                ['user_id' => $currentUser->id],
                                                [
                                                    'name'        => $currentUser->name,
                                                    'email'       => $currentUser->email,
                                                    'affiliation' => null,
                                                ]
                                            );

                                            $set('authorPublications', [[
                                                'author_id'        => $currentAuthor->id,
                                                'is_corresponding' => true,
                                                'order'            => 1,
                                            ]]);
                                        })
                                        ->schema([
                                            Select::make('author_id')
                                                ->label('Author')
                                                ->required()
                                                ->searchable(['name', 'email'])
                                                ->preload()
                                                ->live()
                                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                                ->disabled(function (Get $get): bool {
                                                    return (bool) $get('is_corresponding');
                                                })
                                                ->relationship('author', 'name')
                                                ->dehydrated()
                                                ->createOptionForm([
                                                    \Filament\Forms\Components\TextInput::make('name')
                                                        ->label('Name')
                                                        ->required()
                                                        ->maxLength(255),

                                                    \Filament\Forms\Components\TextInput::make('email')
                                                        ->label('Email')
                                                        ->email()
                                                        ->required()
                                                        ->maxLength(255)
                                                        ->unique(table: 'authors', column: 'email', ignoreRecord: true),

                                                    \Filament\Forms\Components\TextInput::make('affiliation')
                                                        ->label('Affiliation')
                                                        ->maxLength(255)
                                                        ->nullable(),
                                                ])
                                                ->createOptionUsing(fn(array $data) => Author::create($data)->getKey()),

                                            Checkbox::make('is_corresponding')
                                                ->label('Corresponding author')
                                                ->disabled()
                                                ->dehydrated(),
                                        ])
                                        ->mutateDehydratedStateUsing(function (array $state): array {
                                            $state = array_values(array_filter($state, fn($row) => ! empty($row['author_id'])));

                                            $hasCorresponding = collect($state)->contains(fn($row) => (bool) ($row['is_corresponding'] ?? false));

                                            if (! $hasCorresponding && count($state) > 0) {
                                                $state[0]['is_corresponding'] = true;
                                            }

                                            $already = false;
                                            foreach ($state as $i => $row) {
                                                $state[$i]['order'] = $i + 1;
                                                $isCorr = (bool) ($row['is_corresponding'] ?? false);
                                                if ($isCorr && ! $already) {
                                                    $already = true;
                                                    $state[$i]['is_corresponding'] = true;
                                                } else {
                                                    $state[$i]['is_corresponding'] = false;
                                                }
                                            }

                                            return $state;
                                        })
                                        ->columnSpanFull()
                                        ->disabled(fn() => self::isReviewer()),
                                ]),
                        ]),

                    // ─────────────────────────────────────────
                    // STEP 4 — Finalisasi
                    // ─────────────────────────────────────────
                    // ─────────────────────────────────────────
                    // STEP 4 — Finalisasi
                    // ─────────────────────────────────────────
                    Step::make('Finalisasi')
                        ->description('Cover, status, dan tanggal publikasi')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->completedIcon('heroicon-o-check-circle')
                        ->columns(2)
                        ->schema([
                            Section::make('Cover & Files')
                                ->description('Media pendukung publikasi')
                                ->icon('heroicon-o-photo')
                                ->columnSpan(1)
                                ->schema([
                                    FileUpload::make('cover_image_path')
                                        ->label('Cover Image')
                                        ->image()
                                        ->disk('public')
                                        ->directory('publications/covers')
                                        ->visibility('public')
                                        ->imageEditor()                         // ← bisa di-crop/edit seperti di Author
                                        ->imageEditorAspectRatios([
                                            null,                              // bebas
                                            '2:3',                             // portrait buku standar (600×900)
                                        ])
                                        ->imageCropAspectRatio('2:3')           // default crop 2:3
                                        ->imageResizeTargetWidth(600)           // target width 600px
                                        ->imageResizeTargetHeight(900)          // target height 900px
                                        ->imageResizeMode('cover')              // fill seluruh area crop
                                        ->imagePreviewHeight('300')             // preview lebih tinggi agar proporsional
                                        ->maxSize(2048)
                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                        ->helperText('Format: JPG/PNG/WebP. Maks. 2MB. Rasio ideal 2:3 (600×900px).')
                                        ->disabled(fn() => self::isReviewer()),
                                ]),

                            Section::make('Publication Status')
                                ->description('Status proses publikasi')
                                ->icon('heroicon-o-clipboard-document-check')
                                ->columnSpan(1)
                                ->schema([
                                    Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'draft'             => 'Draft',
                                            'submitted'         => 'Submitted',
                                            'in_review'         => 'In Review',
                                            'revision_required' => 'Revision Required',
                                            'accepted'          => 'Accepted',
                                            'rejected'          => 'Rejected',
                                            'published'         => 'Published',
                                        ])
                                        ->default('draft')
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                            if ($state === 'published' && blank($get('published_at'))) {
                                                $set('published_at', now());
                                            }
                                            if ($state !== 'published') {
                                                $set('published_at', null);
                                            }
                                        })
                                        ->disabled(fn() => auth()->user()?->hasRole('author'))
                                        ->dehydrated(),

                                    DateTimePicker::make('published_at')
                                        ->label('Published At')
                                        ->visible(fn(Get $get) => $get('status') === 'published')
                                        ->disabled(fn() => auth()->user()?->hasRole('author'))
                                        ->dehydrated(fn(Get $get) => $get('status') === 'published')
                                        ->helperText('Diisi otomatis saat status berubah ke Published.'),
                                ]),
                        ]),

                    // ─────────────────────────────────────────
                    // STEP 5 — Preview
                    // ─────────────────────────────────────────
                    Step::make('Preview')
                        ->description('Cek tampilan sebelum simpan')
                        ->icon('heroicon-o-eye')
                        ->completedIcon('heroicon-o-check-circle')
                        ->schema([
                            View::make('filament.publications.preview')
                                ->viewData([
                                    'titleLabel' => 'Judul',
                                ]),
                        ]),

                ])
                    ->skippable()                              // ← icon step bisa diklik bebas
                    ->persistStepInQueryString()               // ← step tersimpan di URL saat refresh
                    ->nextAction(
                        fn(Action $action) => $action
                            ->label('Lanjut')
                            ->icon('heroicon-o-arrow-right')
                            ->iconPosition('after')
                    )
                    ->previousAction(
                        fn(Action $action) => $action
                            ->label('Kembali')
                            ->icon('heroicon-o-arrow-left')
                            ->color('gray')
                    ),
            ]);
    }
}

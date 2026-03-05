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
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class PublicationForm
{
    private static function isReviewer(): bool
    {
        return (bool) auth()->user()?->hasRole('reviewer');
    }

    private static function publicationTypeSlug(callable $get): ?string
    {
        $id = $get('publication_type_id');
        if (!$id) return null;

        return PublicationType::query()->whereKey($id)->value('slug');
    }

    /**
     * ✅ Helper: ambil atau buat Author profile untuk user yang sedang login
     * Selalu load relasi user agar accessor name bisa resolve
     */
    private static function resolveCurrentAuthor(): ?Author
    {
        $currentUser = auth()->user();
        if (!$currentUser) return null;

        $author = Author::firstOrCreate(
            ['user_id' => $currentUser->id],
            [
                'name'        => null,
                'email'       => null,
                'affiliation' => null,
                'bio'         => null,
                'photo_path'  => null,
            ]
        );

        // ✅ Paksa load relasi user agar accessor name bisa resolve
        $author->setRelation('user', $currentUser);

        return $author;
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

                                    TextInput::make('title')
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

                                    // Jurnal → Abstrak
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
                                        ->helperText('Wajib. Tulis abstrak sesuai standar jurnal.')
                                        ->disabled(fn() => self::isReviewer()),

                                    // Buku → Sinopsis
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
                                        ->helperText('Opsional. Tulis sinopsis menarik.')
                                        ->disabled(fn() => self::isReviewer()),

                                    // Opini → Isi Opini
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
                                        ->helperText('Wajib. Tulis isi opini secara lengkap.')
                                        ->disabled(fn() => self::isReviewer()),

                                    // Keywords — Jurnal
                                    Select::make('keywords')
                                        ->label('Keywords')
                                        ->relationship('keywords', 'name', fn($query) => $query->orderBy('name'))
                                        ->multiple()->searchable()->preload()
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->required(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->createOptionForm([
                                            TextInput::make('name')->label('Keyword')->required()->maxLength(100)
                                                ->live(onBlur: true)->unique(table: 'keywords', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                            TextInput::make('slug')->label('Slug')->required()->disabled()->dehydrated(),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Keyword::create($data)->getKey())
                                        ->helperText('Wajib. Pilih 3–7 keyword.')
                                        ->disabled(fn() => self::isReviewer())->columnSpanFull(),

                                    // Tags — Buku
                                    Select::make('keywords')
                                        ->label('Tags')
                                        ->relationship('keywords', 'name', fn($query) => $query->orderBy('name'))
                                        ->multiple()->searchable()->preload()
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'buku')
                                        ->required(false)
                                        ->createOptionForm([
                                            TextInput::make('name')->label('Tag')->required()->maxLength(100)
                                                ->live(onBlur: true)->unique(table: 'keywords', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                            TextInput::make('slug')->label('Slug')->required()->disabled()->dehydrated(),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Keyword::create($data)->getKey())
                                        ->helperText('Opsional.')
                                        ->disabled(fn() => self::isReviewer())->columnSpanFull(),

                                    // Topik — Opini
                                    Select::make('keywords')
                                        ->label('Topik')
                                        ->relationship('keywords', 'name', fn($query) => $query->orderBy('name'))
                                        ->multiple()->searchable()->preload()->maxItems(3)
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'opini')
                                        ->required(false)
                                        ->createOptionForm([
                                            TextInput::make('name')->label('Topik')->required()->maxLength(100)
                                                ->live(onBlur: true)->unique(table: 'keywords', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                            TextInput::make('slug')->label('Slug')->required()->disabled()->dehydrated(),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Keyword::create($data)->getKey())
                                        ->helperText('Opsional. Maks. 3 topik.')
                                        ->disabled(fn() => self::isReviewer())->columnSpanFull(),
                                ]),
                        ]),

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

                                    Select::make('categories')
                                        ->label('Category')
                                        ->relationship('categories', 'name', fn($query) => $query->orderBy('name'))
                                        ->multiple()->maxItems(1)->searchable()->preload()->required()
                                        ->createOptionForm([
                                            TextInput::make('name')->label('Category Name')->required()->maxLength(100)
                                                ->live(onBlur: true)->unique(table: 'categories', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                            TextInput::make('slug')->label('Slug')->required()->disabled()->dehydrated(),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Category::create($data)->getKey())
                                        ->helperText('Pilih 1 kategori.')
                                        ->disabled(fn() => self::isReviewer())->columnSpan(1),

                                    Select::make('method_id')
                                        ->label(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Research Method',
                                            'buku'   => 'Metode Penulisan',
                                            default  => 'Research Method',
                                        })
                                        ->relationship('method', 'name', fn($query) => $query->orderBy('name'))
                                        ->searchable()->preload()
                                        ->visible(fn($get) => self::publicationTypeSlug($get) !== 'opini')
                                        ->required(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->createOptionForm([
                                            TextInput::make('name')->label('Method Name')->required()->maxLength(100)
                                                ->live(onBlur: true)->unique(table: 'methods', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                            TextInput::make('slug')->label('Slug')->required()->disabled()->dehydrated(),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Method::create($data)->getKey())
                                        ->helperText(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Wajib. Pilih metode penelitian.',
                                            'buku'   => 'Opsional.',
                                            default  => '',
                                        })
                                        ->disabled(fn() => self::isReviewer())->columnSpan(1),

                                    Placeholder::make('method_info')
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
                                ->description('Penulis corresponding diisi otomatis dari akun yang login.')
                                ->icon('heroicon-o-users')
                                ->schema([
                                    Repeater::make('authorPublications')
                                        ->label('Authors')
                                        ->deletable(false)
                                        ->relationship('authorPublications')
                                        ->orderColumn('order')
                                        ->reorderable()
                                        ->defaultItems(0)   // ✅ Jangan pakai default, kita set manual
                                        ->minItems(1)
                                        ->addActionLabel('Tambah penulis lain')
                                        ->collapsed()
                                        ->afterStateHydrated(function (?array $state, callable $set) {
                                            $state ??= [];
                                            $state = array_values($state);

                                            // ✅ Jika sudah ada data (mode edit), skip init
                                            if (count($state) > 0) {
                                                foreach ($state as $i => $row) {
                                                    $state[$i]['order'] = $i + 1;
                                                }
                                                $set('authorPublications', $state);
                                                return;
                                            }

                                            // ✅ Init: ambil/buat Author profile untuk user yang login
                                            $author = self::resolveCurrentAuthor();
                                            if (!$author) return;

                                            // ✅ Set HANYA 1 item dengan author dari user login
                                            $set('authorPublications', [[
                                                'author_id'        => $author->id,
                                                'is_corresponding' => true,
                                                'order'            => 1,
                                            ]]);
                                        })
                                        ->schema([
                                            Select::make('author_id')
                                                ->label('Author')
                                                ->required()
                                                ->live()
                                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                                // ✅ Corresponding author tidak bisa diganti
                                                ->disabled(fn(Get $get): bool => (bool) $get('is_corresponding'))
                                                ->relationship('author', 'name')
                                                ->searchable()
                                                // ✅ FIXED: Search gabungan authors + users dengan load('user')
                                                ->getSearchResultsUsing(function (string $search) {
                                                    return Author::query()
                                                        ->with('user')
                                                        ->where(function ($q) use ($search) {
                                                            $q->where('authors.name', 'like', "%{$search}%")
                                                                ->orWhere('authors.email', 'like', "%{$search}%")
                                                                ->orWhereHas(
                                                                    'user',
                                                                    fn($u) =>
                                                                    $u->where('name', 'like', "%{$search}%")
                                                                        ->orWhere('email', 'like', "%{$search}%")
                                                                );
                                                        })
                                                        ->limit(20)
                                                        ->get()
                                                        ->mapWithKeys(function (Author $author) {
                                                            // ✅ Accessor name sudah resolved karena with('user')
                                                            $label = $author->name;
                                                            if ($author->email) $label .= " — {$author->email}";
                                                            if ($author->affiliation) $label .= " ({$author->affiliation})";
                                                            return [$author->id => $label];
                                                        });
                                                })
                                                // ✅ FIXED: getOptionLabelUsing dengan with('user')
                                                ->getOptionLabelUsing(function ($value): string {
                                                    $author = Author::with('user')->find($value);
                                                    if (!$author) return '—';

                                                    // ✅ Jika linked ke user, set relasi manual agar accessor resolve
                                                    $label = $author->name; // accessor sudah resolve karena with('user')
                                                    if ($author->email) $label .= " — {$author->email}";
                                                    return $label;
                                                })
                                                ->dehydrated()
                                                // ✅ Create form — untuk tambah external author
                                                ->createOptionForm([
                                                    TextInput::make('name')
                                                        ->label('Nama Lengkap')
                                                        ->required()
                                                        ->maxLength(255)
                                                        ->helperText('Untuk external author yang tidak punya akun.'),

                                                    TextInput::make('email')
                                                        ->label('Email')
                                                        ->email()
                                                        ->maxLength(255)
                                                        ->unique(table: 'authors', column: 'email', ignoreRecord: true)
                                                        ->helperText('Opsional.'),

                                                    TextInput::make('affiliation')
                                                        ->label('Affiliasi / Institusi')
                                                        ->maxLength(255),

                                                    Textarea::make('bio')
                                                        ->label('Bio Singkat')
                                                        ->rows(3)
                                                        ->maxLength(500),
                                                ])
                                                ->createOptionUsing(fn(array $data) => Author::create($data)->getKey()),

                                            Checkbox::make('is_corresponding')
                                                ->label('Corresponding author')
                                                ->disabled()
                                                ->dehydrated(),
                                        ])
                                        ->mutateDehydratedStateUsing(function (array $state): array {
                                            $state = array_values(array_filter(
                                                $state,
                                                fn($row) => !empty($row['author_id'])
                                            ));

                                            $hasCorresponding = collect($state)->contains(
                                                fn($row) => (bool) ($row['is_corresponding'] ?? false)
                                            );

                                            if (!$hasCorresponding && count($state) > 0) {
                                                $state[0]['is_corresponding'] = true;
                                            }

                                            $already = false;
                                            foreach ($state as $i => $row) {
                                                $state[$i]['order'] = $i + 1;
                                                $isCorr = (bool) ($row['is_corresponding'] ?? false);
                                                if ($isCorr && !$already) {
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
                                        ->imageEditor()
                                        ->imageEditorAspectRatios([null, '2:3'])
                                        ->imageCropAspectRatio('2:3')
                                        ->imageResizeTargetWidth(600)
                                        ->imageResizeTargetHeight(900)
                                        ->imageResizeMode('cover')
                                        ->imagePreviewHeight('300')
                                        ->maxSize(2048)
                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                        ->helperText('Format: JPG/PNG/WebP. Maks. 2MB. Rasio ideal 2:3.')
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
                                ->viewData(['titleLabel' => 'Judul']),
                        ]),

                ])
                    ->skippable()
                    ->persistStepInQueryString()
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

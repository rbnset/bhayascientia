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
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Schemas\Components\View;

class PublicationForm
{
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
                    // =========================
                    // STEP 1: BASIC INFO
                    // =========================
                    Step::make('Informasi publikasi')
                        ->description('Tipe, judul, ringkasan, dan keyword')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Section::make('Publication Information')
                                ->description('Informasi utama karya ilmiah')
                                ->icon('heroicon-o-document-text')
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
                                        ->helperText('Pilih tipe publikasi terlebih dahulu agar field lain menyesuaikan.'),

                                    TextInput::make('title')
                                        ->label(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Judul Artikel Jurnal',
                                            'buku'   => 'Judul Buku',
                                            'opini'  => 'Judul Opini',
                                            default  => 'Judul Publikasi',
                                        })
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('Tulis judul yang jelas dan ringkas.'),

                                    Textarea::make('abstract')
                                        ->rows(6)
                                        ->columnSpanFull()
                                        ->label(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Abstrak',
                                            'buku'   => 'Ringkasan',
                                            default  => 'Ringkasan',
                                        })
                                        ->visible(fn($get) => self::publicationTypeSlug($get) !== 'opini')
                                        ->required(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->helperText(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Abstrak wajib sesuai standar artikel jurnal ilmiah.',
                                            'buku'   => 'Ringkasan isi buku (opsional).',
                                            default  => 'Ringkasan singkat (opsional).',
                                        }),

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
                                            TextInput::make('name')
                                                ->label('Keyword')
                                                ->required()
                                                ->maxLength(100)
                                                ->live(onBlur: true)
                                                ->unique(table: 'keywords', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),

                                            TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->disabled()
                                                ->dehydrated(),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Keyword::create($data)->getKey())
                                        ->helperText('Wajib untuk jurnal. Pilih 3–7 keyword yang mewakili konsep utama penelitian.'),
                                ]),
                        ]),

                    // =========================
                    // STEP 2: CLASSIFICATION
                    // =========================
                    Step::make('Klasifikasi')
                        ->description('Kategori & metode penelitian')
                        ->icon('heroicon-o-tag')
                        ->schema([
                            Section::make('Classification')
                                ->description('Kategori dan metode penelitian')
                                ->icon('heroicon-o-tag')
                                ->schema([
                                    Select::make('categories')
                                        ->label('Categories')
                                        ->relationship(
                                            name: 'categories',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn($query) => $query->orderBy('name'),
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
                                                ->unique(table: 'categories', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),

                                            TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->disabled()
                                                ->dehydrated(),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Category::create($data)->getKey())
                                        ->helperText('Pilih kategori yang paling relevan (boleh lebih dari satu).'),

                                    Select::make('method_id')
                                        ->label('Research Method')
                                        ->relationship(
                                            name: 'method',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn($query) => $query->orderBy('name'),
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('Method Name')
                                                ->required()
                                                ->maxLength(100)
                                                ->live(onBlur: true)
                                                ->unique(table: 'methods', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),

                                            TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->disabled()
                                                ->dehydrated(),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Method::create($data)->getKey())
                                        ->helperText('Pilih metode penelitian utama.'),
                                ]),
                        ]),

                    // =========================
                    // STEP 3: AUTHORS
                    // =========================
                    Step::make('Penulis')
                        ->description('Tambah penulis & urutkan dengan drag & drop')
                        ->icon('heroicon-o-users')
                        ->schema([
                            Section::make('Authors')
                                ->description('Tambah penulis dan atur urutan dengan drag & drop')
                                ->icon('heroicon-o-users')
                                ->schema([
                                    Repeater::make('authorPublications')
                                        ->label('Authors')
                                        ->relationship('authorPublications')
                                        ->orderColumn('order')
                                        ->reorderable()
                                        ->collapsed()
                                        ->defaultItems(1)
                                        ->addActionLabel('Tambah penulis')
                                        ->itemLabel(function (array $state): ?string {
                                            if (! isset($state['author_id'])) {
                                                return 'Penulis';
                                            }

                                            $author = Author::query()->find($state['author_id']);

                                            return $author?->name ?: 'Penulis';
                                        })
                                        ->schema([
                                            Select::make('author_id')
                                                ->label('Author')
                                                ->relationship('author', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->helperText('Urutkan penulis dengan drag & drop pada item author.')
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
                                        ])
                                        ->mutateDehydratedStateUsing(function (array $state): array {
                                            $authorId = Author::query()
                                                ->where('user_id', auth()->id())
                                                ->value('id');

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
                        ]),

                    // =========================
                    // STEP 4: FINALISASI
                    // =========================
                    Step::make('Finalisasi')
                        ->description('Cover, status, dan tanggal publikasi')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->schema([
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
                                        ->visible(fn($get) => $get('status') === 'published'),
                                ]),
                        ]),

                    // =========================
                    // STEP 5: PREVIEW
                    // =========================

                    Step::make('Preview')
                        ->description('Cek tampilan ringkas sebelum simpan')
                        ->icon('heroicon-o-eye')
                        ->schema([
                            View::make('filament.publications.preview')
                                ->viewData([
                                    // hanya label, untuk dipakai di Blade
                                    'titleLabel' => 'Judul',
                                ]),
                        ]),

                ])
                    ->persistStepInQueryString(),
            ]);
    }
}

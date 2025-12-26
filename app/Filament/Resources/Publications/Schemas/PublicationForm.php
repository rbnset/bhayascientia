<?php

namespace App\Filament\Resources\Publications\Schemas;

use App\Models\Author;
use App\Models\Category;
use App\Models\Keyword;
use App\Models\Method;
use App\Models\PublicationType;
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
                                        ->helperText('Pilih tipe publikasi terlebih dahulu agar field lain menyesuaikan.')
                                        ->disabled(fn() => self::isReviewer()),

                                    \Filament\Forms\Components\TextInput::make('title')
                                        ->label(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Judul Artikel Jurnal',
                                            'buku'   => 'Judul Buku',
                                            'opini'  => 'Judul Opini',
                                            default  => 'Judul Publikasi',
                                        })
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('Tulis judul yang jelas dan ringkas.')
                                        ->disabled(fn() => self::isReviewer()),

                                    RichEditor::make('abstract')
                                        ->columnSpanFull()
                                        ->label(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Abstrak',
                                            'buku'   => 'Ringkasan',
                                            default  => 'Ringkasan',
                                        })
                                        ->visible(fn($get) => self::publicationTypeSlug($get) !== 'opini')
                                        ->required(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->toolbarButtons([
                                            ['bold', 'italic', 'underline', 'strike', 'link'],
                                            ['bulletList', 'orderedList', 'blockquote'],
                                            ['undo', 'redo'],
                                        ])
                                        ->helperText(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Abstrak wajib sesuai standar artikel jurnal ilmiah.',
                                            'buku'   => 'Ringkasan isi buku (opsional).',
                                            default  => 'Ringkasan singkat (opsional).',
                                        })
                                        ->disabled(fn() => self::isReviewer()),

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
                                        ->helperText('Wajib untuk jurnal. Pilih 3–7 keyword yang mewakili konsep utama penelitian.')
                                        ->disabled(fn() => self::isReviewer()),
                                ]),
                        ]),

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
                                        ->helperText('Pilih kategori yang paling relevan (boleh lebih dari satu).')
                                        ->disabled(fn() => self::isReviewer()),

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
                                        ->helperText('Pilih metode penelitian utama.')
                                        ->disabled(fn() => self::isReviewer()),
                                ]),
                        ]),

                    Step::make('Penulis')
                        ->description('Penulis utama otomatis + tambah penulis lain (tanpa dobel)')
                        ->icon('heroicon-o-users')
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
                                                    'name' => $currentUser->name,
                                                    'email' => $currentUser->email,
                                                    'affiliation' => null,
                                                ]
                                            );

                                            $state = [[
                                                'author_id' => $currentAuthor->id,
                                                'is_corresponding' => true,
                                                'order' => 1,
                                            ]];

                                            $set('authorPublications', $state);
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
                                                    // kunci corresponding author (seperti kode Anda)
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
                                        // reviewer tidak boleh ubah relasi authors sama sekali
                                        ->disabled(fn() => self::isReviewer()),
                                ]),
                        ]),

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
                                        ->disk('public')
                                        ->directory('publications/covers')
                                        ->visibility('public')
                                        ->imagePreviewHeight('200')
                                        ->maxSize(2048)
                                        ->disabled(fn() => self::isReviewer()),
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
                                        ->required()
                                        // author tidak boleh ubah status (sesuai kode Anda)
                                        ->disabled(fn() => auth()->user()?->hasRole('author'))
                                        ->dehydrated(),

                                    DateTimePicker::make('published_at')
                                        ->label('Published At')
                                        ->visible(fn($get) => $get('status') === 'published')
                                        ->disabled(fn() => self::isReviewer()),
                                ]),
                        ]),

                    Step::make('Preview')
                        ->description('Cek tampilan ringkas sebelum simpan')
                        ->icon('heroicon-o-eye')
                        ->schema([
                            View::make('filament.publications.preview')
                                ->viewData([
                                    'titleLabel' => 'Judul',
                                ]),
                        ]),
                ])->persistStepInQueryString(),
            ]);
    }
}

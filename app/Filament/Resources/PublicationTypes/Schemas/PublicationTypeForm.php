<?php

namespace App\Filament\Resources\PublicationTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Group;
use Filament\Schemas\Components\Group as ComponentsGroup;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PublicationTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                // =========================
                // PUBLICATION TYPE DETAILS
                // =========================
                Section::make('Publication Type Details')
                    ->description('Pengaturan jenis publikasi karya ilmiah')
                    ->icon('heroicon-o-document-text')
                    ->collapsed(false)
                    ->schema([

                        TextInput::make('name')
                            ->label('Publication Type Name')
                            ->placeholder('Contoh: Jurnal Ilmiah')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->helperText('Nama jenis publikasi.')
                            ->afterStateUpdated(function ($state, callable $set, $record) {
                                if (! $record) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->placeholder('jurnal-ilmiah')
                            ->required()
                            ->maxLength(120)
                            ->helperText('Digunakan untuk URL, dibuat otomatis.')
                            ->unique(ignoreRecord: true)
                            ->disabled()
                            ->dehydrated(),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Deskripsi singkat jenis publikasi...')
                            ->rows(3)
                            ->helperText('Opsional, untuk penjelasan tambahan.')
                            ->columnSpanFull(),

                        Toggle::make('requires_review')
                            ->label('Requires Review')
                            ->helperText('Apakah publikasi ini memerlukan proses review?')
                            ->default(true)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Aktifkan agar dapat digunakan dalam sistem.')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),

                // =========================
                // PUBLICATION TYPE CONTENT (ONE-TO-ONE)
                // =========================
                Section::make('Content Information')
                    ->description('Informasi konten untuk jenis publikasi ini')
                    ->icon('heroicon-o-document-duplicate')
                    ->collapsed(false)
                    ->schema([
                        ComponentsGroup::make()
                            ->relationship('content')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Content Title')
                                    ->placeholder('Contoh: Panduan Penulisan Jurnal')
                                    ->maxLength(255)
                                    ->helperText('Judul konten untuk jenis publikasi ini.')
                                    ->columnSpanFull(),

                                Textarea::make('description')
                                    ->label('Content Description')
                                    ->placeholder('Deskripsi lengkap tentang konten...')
                                    ->rows(5)
                                    ->helperText('Penjelasan detail tentang konten publikasi.')
                                    ->columnSpanFull(),

                                FileUpload::make('image_path')
                                    ->label('Content Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('publication-types/content-images')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->maxSize(3072)
                                    ->helperText('Format: JPG, PNG. Maksimal 3MB. Rasio: 16:9, 4:3, atau 1:1')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                    ->moveFiles()
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->columns(1),
            ]);
    }
}

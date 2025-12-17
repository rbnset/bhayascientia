<?php

namespace App\Filament\Resources\Publications\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PublicationForm
{
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
                            ->relationship('publicationType', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->placeholder('Contoh: Analisis Dampak AI dalam Pendidikan'),

                        Textarea::make('abstract')
                            ->label('Abstract')
                            ->rows(6)
                            ->required()
                            ->placeholder('Tuliskan ringkasan karya ilmiah...')
                            ->columnSpanFull(),
                    ]),

                // =========================
                // CLASSIFICATION
                // =========================
                Section::make('Classification')
                    ->description('Kategori dan metode penelitian')
                    ->icon('heroicon-o-tag')
                    ->schema([

                        Select::make('categories')
                            ->label('Categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),

                        Select::make('method_id')
                            ->label('Research Method')
                            ->relationship('method', 'name')
                            ->searchable()
                            ->preload(),
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
                            ->maxSize(2048)
                            ->helperText('Opsional · JPG/PNG · Maks 2MB'),
                    ]),

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
                            ->visible(fn($get) => $get('status') === 'published'),
                    ]),
            ]);
    }
}

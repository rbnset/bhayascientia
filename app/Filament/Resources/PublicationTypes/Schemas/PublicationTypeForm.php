<?php

namespace App\Filament\Resources\PublicationTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
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
            ]);
    }
}

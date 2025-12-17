<?php

namespace App\Filament\Resources\DownloadLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DownloadLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                // =========================
                // DOWNLOAD LOG DETAILS
                // =========================
                Section::make('Download Log Details')
                    ->description('Catatan unduhan karya ilmiah')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->collapsed(false)
                    ->schema([

                        Select::make('publication_id')
                            ->label('Publication')
                            ->relationship('publication', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Publikasi yang diunduh.'),

                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Pengguna yang mengunduh (kosong jika tamu).'),

                        DateTimePicker::make('downloaded_at')
                            ->label('Downloaded At')
                            ->required()
                            ->seconds(false)
                            ->default(now())
                            ->helperText('Waktu terjadinya unduhan.'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 1,
                    ]),
            ]);
    }
}

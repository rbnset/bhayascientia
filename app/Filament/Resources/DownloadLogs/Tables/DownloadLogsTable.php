<?php

namespace App\Filament\Resources\DownloadLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DownloadLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // =====================
                // PUBLICATION (PRIMARY)
                // =====================
                TextColumn::make('publication.title')
                    ->label('Publication')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn($record) => $record->publication?->slug),

                // =====================
                // USER
                // =====================
                TextColumn::make('user.name')
                    ->label('Downloaded By')
                    ->placeholder('Guest')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->user?->email),

                // =====================
                // DOWNLOADED AT (PRIMARY META)
                // =====================
                TextColumn::make('downloaded_at')
                    ->label('Downloaded At')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since(),

                // =====================
                // CREATED AT (SYSTEM)
                // =====================
                TextColumn::make('created_at')
                    ->label('Logged At')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('downloaded_at', 'desc')
            ->filters([
                // future: date / publication filters
            ])
            ->recordActions([
                // ❌ log seharusnya tidak diedit
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash'),
                ]),
            ]);
    }
}

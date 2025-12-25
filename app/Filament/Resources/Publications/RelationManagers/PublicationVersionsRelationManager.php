<?php

namespace App\Filament\Resources\Publications\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PublicationVersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    protected static ?string $title = 'Publication Versions';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('version_number', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('version_number')
                    ->label('Version')
                    ->sortable(),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pdf_file_path')
                    ->label('Manuscript')
                    ->formatStateUsing(fn() => 'View PDF')
                    ->url(fn($record) => route('manuscripts.view', $record))
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('download_link')
                    ->label('Download')
                    ->state(fn() => 'Download')
                    ->url(fn($record) => route('manuscripts.download', $record))
                    ->openUrlInNewTab(),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}

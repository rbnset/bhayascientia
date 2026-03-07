<?php

namespace App\Filament\Resources\Publications\RelationManagers;

use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
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

                // ── Nomor versi + badge "Latest" ───────────────────────────
                TextColumn::make('version_number')
                    ->label('Version')
                    ->sortable()
                    ->badge()
                    ->color(
                        fn($record) => $record->version_number === $this->getOwnerRecord()
                            ->versions()
                            ->max('version_number')
                            ? 'success'
                            : 'gray'
                    )
                    ->formatStateUsing(
                        fn($state, $record) =>
                        'v' . $state .
                            ($record->version_number === $this->getOwnerRecord()->versions()->max('version_number')
                                ? '  ★ Latest'
                                : '')
                    ),

                // ── Tanggal submit (WIB) ───────────────────────────────────
                TextColumn::make('submitted_at')
                    ->label('Submitted At')
                    ->sortable()
                    ->placeholder('—')
                    ->formatStateUsing(
                        fn($state) => $state
                            ? \Carbon\Carbon::parse($state)
                            ->setTimezone('Asia/Jakarta')
                            ->translatedFormat('d M Y, H:i') . ' WIB'
                            : '—'
                    )
                    ->tooltip(
                        fn($record) => $record->submitted_at
                            ? \Carbon\Carbon::parse($record->submitted_at)
                            ->setTimezone('Asia/Jakarta')
                            ->diffForHumans()
                            : null
                    ),

                // ── Tanggal dibuat (WIB) ───────────────────────────────────
                TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->sortable()
                    ->formatStateUsing(
                        fn($state) => $state
                            ? \Carbon\Carbon::parse($state)
                            ->setTimezone('Asia/Jakarta')
                            ->translatedFormat('d M Y, H:i') . ' WIB'
                            : '—'
                    )
                    ->tooltip(
                        fn($record) => \Carbon\Carbon::parse($record->created_at)
                            ->setTimezone('Asia/Jakarta')
                            ->diffForHumans()
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                // ── Indikator file ada/tidak ───────────────────────────────
                IconColumn::make('pdf_file_path')
                    ->label('File')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('primary')
                    ->falseColor('danger')
                    ->tooltip(
                        fn($record) => $record->pdf_file_path
                            ? 'File available'
                            : 'No file uploaded'
                    ),
            ])

            ->actions([
                Action::make('view_pdf')
                    ->label('Read')
                    ->icon('heroicon-o-book-open')
                    ->color('info')
                    ->url(fn($record) => route('manuscripts.view', $record))
                    ->openUrlInNewTab()
                    ->visible(fn($record) => !empty($record->pdf_file_path)),

                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn($record) => route('manuscripts.download', $record))
                    ->openUrlInNewTab()
                    ->visible(fn($record) => !empty($record->pdf_file_path)),
            ])

            ->bulkActions([])

            ->emptyStateIcon('heroicon-o-document')
            ->emptyStateHeading('No versions yet')
            ->emptyStateDescription('Versions will appear here once the author submits a manuscript.');
    }
}

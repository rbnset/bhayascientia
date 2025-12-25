<?php

namespace App\Filament\Resources\Publications\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Reviews & Feedback';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('publicationVersion.version_number')
                    ->label('Version')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('decision')
                    ->label('Decision')
                    ->badge()
                    ->colors([
                        'warning' => 'revision_required',
                        'success' => 'accepted',
                        'danger'  => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reviewed at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('overall_comment')
                    ->label('Overall comment')
                    ->limit(60),
            ])
            // Untuk author: read-only, tidak bisa create/edit/delete review dari sini.
            ->actions([
                ViewAction::make()
                    ->label('Lihat')
                    ->slideOver()
                    ->infolist([
                        Section::make('Hasil Review')
                            ->schema([
                                TextEntry::make('publicationVersion.version_number')
                                    ->label('Version'),

                                TextEntry::make('reviewer.name')
                                    ->label('Reviewer'),

                                TextEntry::make('decision')
                                    ->label('Decision')
                                    ->badge()
                                    ->color(fn(string $state) => match ($state) {
                                        'revision_required' => 'warning',
                                        'accepted' => 'success',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    }),

                                TextEntry::make('created_at')
                                    ->label('Reviewed at')
                                    ->dateTime(),
                            ]),

                        Section::make('Komentar Umum')
                            ->schema([
                                TextEntry::make('overall_comment')
                                    ->label('Overall comment')
                                    ->markdown(),
                            ]),

                        Section::make('Catatan per Bagian')
                            ->schema([
                                RepeatableEntry::make('notes')
                                    ->schema([
                                        TextEntry::make('section')
                                            ->label('Section')
                                            ->badge(),

                                        TextEntry::make('note')
                                            ->label('Note')
                                            ->markdown(),
                                    ]),
                            ]),

                        Section::make('Lampiran (PDF)')
                            ->schema([
                                RepeatableEntry::make('attachments')
                                    ->schema([
                                        TextEntry::make('file_path')
                                            ->label('File')
                                            ->formatStateUsing(fn() => 'Download / Buka')
                                            ->url(fn($record) => \Illuminate\Support\Facades\Storage::disk('public')->url($record))
                                            ->openUrlInNewTab(),
                                    ]),
                            ]),
                    ]),
            ])
            ->headerActions([])
            ->bulkActions([]);
    }
}

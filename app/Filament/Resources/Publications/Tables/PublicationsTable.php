<?php

namespace App\Filament\Resources\Publications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PublicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // =====================
                // COVER
                // =====================
                ImageColumn::make('cover_image_path')
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl('/images/placeholder-publication.png'),

                // =====================
                // TITLE (PRIMARY)
                // =====================
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(
                        fn($record) =>
                        $record->publicationType?->name
                    ),

                // =====================
                // AUTHORS (PREVIEW)
                // =====================
                TextColumn::make('authors')
                    ->label('Authors')
                    ->getStateUsing(
                        fn($record) =>
                        $record->authors
                            ->take(3)
                            ->pluck('name')
                            ->join(', ')
                    )
                    ->placeholder('—'),

                // =====================
                // CATEGORIES
                // =====================
                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge()
                    ->separator(', ')
                    ->color('primary'),

                // =====================
                // METHOD
                // =====================
                TextColumn::make('method.name')
                    ->label('Method')
                    ->badge()
                    ->color('gray'),

                // =====================
                // STATUS
                // =====================
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'submitted',
                        'info' => 'in_review',
                        'danger' => 'revision_required',
                        'success' => ['accepted', 'published'],
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn($state) => str($state)->headline()),

                // =====================
                // PUBLISHED AT
                // =====================
                TextColumn::make('published_at')
                    ->label('Published')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—'),

                // =====================
                // SYSTEM
                // =====================
                TextColumn::make('created_at')
                    ->label('Created')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Deleted')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'in_review' => 'In Review',
                        'revision_required' => 'Revision Required',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'published' => 'Published',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}

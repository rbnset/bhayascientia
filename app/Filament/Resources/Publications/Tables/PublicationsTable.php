<?php

namespace App\Filament\Resources\Publications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
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
                // COVER (BOOK PORTRAIT)
                // =====================
                ImageColumn::make('cover_image_path')
                    ->label('')
                    ->disk('public')
                    ->defaultImageUrl(url('/images/placeholder-publication.png'))
                    ->width(44)
                    ->height(64)
                    ->extraImgAttributes([
                        'class' => 'object-cover rounded-md ring-1 ring-gray-200 dark:ring-gray-700',
                    ]),

                // =====================
                // TITLE + TYPE (below)
                // =====================
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap()
                    ->lineClamp(3) // max 3 baris [page:8]
                    ->words(14, end: '...') // potong per kata + "..." [page:8]
                    // hover: tampilkan judul lengkap (tanpa syarat) [page:8]
                    ->tooltip(fn(TextColumn $column): ?string => (string) $column->getState())
                    // type publication tetap tampil
                    ->description(fn($record) => $record->publicationType?->name),

                // =====================
                // AUTHORS
                // =====================
                TextColumn::make('authors.name')
                    ->label('Authors')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->searchable(),

                // =====================
                // CATEGORIES
                // =====================
                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge()
                    ->separator(', ')
                    ->color('primary')
                    ->limitList(3)
                    ->listWithLineBreaks(),

                // =====================
                // METHOD
                // =====================
                TextColumn::make('method.name')
                    ->label('Method')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                // =====================
                // STATUS (OWN COLUMN AGAIN)
                // =====================
                TextColumn::make('status')
                    ->label('Status')
                    ->badge() // badge untuk status [page:8]
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'in_review' => 'info',
                        'revision_required' => 'danger',
                        'accepted', 'published' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => str($state)->headline()) // formatting teks [page:8]
                    ->sortable(),

                // =====================
                // DATES
                // =====================
                TextColumn::make('published_at')
                    ->label('Published')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

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
                    ->preload(),

                SelectFilter::make('publication_type_id')
                    ->label('Publication Type')
                    ->relationship('publicationType', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('method_id')
                    ->label('Method')
                    ->relationship('method', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->label('View')
                    ->slideOver(),

                EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->label('Edit'),
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

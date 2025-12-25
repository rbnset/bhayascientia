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
                // COVER (SQUARE)
                // =====================
                ImageColumn::make('cover_image_path')
                    ->label('')
                    ->disk('public')
                    ->square() // kotak 1:1 [web:499]
                    ->size(44)
                    ->defaultImageUrl(url('/images/placeholder-publication.png')),

                // =====================
                // TITLE (PRIMARY)
                // =====================
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap()
                    ->description(fn($record) => $record->publicationType?->name),

                // =====================
                // AUTHORS (RELATION)
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
                // STATUS
                // =====================
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'submitted',
                        'info' => 'in_review',
                        'danger' => 'revision_required',
                        'success' => ['accepted', 'published'],
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn($state) => str($state)->headline())
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

                // filter relasi (lebih enak untuk admin)
                SelectFilter::make('publication_type_id')
                    ->label('Publication Type')
                    ->relationship('publicationType', 'name')
                    ->searchable()
                    ->preload(), // preload opsi relasi untuk UX [web:500]

                SelectFilter::make('method_id')
                    ->label('Method')
                    ->relationship('method', 'name')
                    ->searchable()
                    ->preload(), // preload opsi relasi untuk UX [web:500]
            ])
            ->recordActions([
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->label('View')
                    ->slideOver(), // cepat, tidak pindah halaman [web:265]

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

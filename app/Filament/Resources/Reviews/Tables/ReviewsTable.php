<?php

namespace App\Filament\Resources\Reviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // =====================
                // PUBLICATION VERSION
                // =====================
                TextColumn::make('publicationVersion.display_label')
                    ->label('Version')
                    ->sortable()
                    ->searchable(),

                // =====================
                // REVIEWER
                // =====================
                TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->searchable()
                    ->sortable(),

                // =====================
                // DECISION
                // =====================
                TextColumn::make('decision')
                    ->label('Decision')
                    ->badge()
                    ->colors([
                        'warning' => 'revision_required',
                        'success' => 'accepted',
                        'danger'  => 'rejected',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'revision_required' => 'Revision Required',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        default => '-',
                    }),

                // =====================
                // CREATED AT
                // =====================
                TextColumn::make('created_at')
                    ->label('Reviewed At')
                    ->date()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

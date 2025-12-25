<?php

namespace App\Filament\Resources\Publications\RelationManagers;

use Filament\Actions\ViewAction as ActionsViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Contracts\View\View as ViewContract;

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
            ->actions([
                ActionsViewAction::make()
                    ->label('Lihat')
                    ->slideOver()
                    ->modalHeading('Review detail')
                    ->modalSubmitAction(false)   // hilangkan tombol submit
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function ($record): ViewContract {
                        // $record = Review model
                        return view('filament.reviews.preview', [
                            'review' => $record,
                        ]);
                    }),
            ])
            ->headerActions([])
            ->bulkActions([]);
    }
}

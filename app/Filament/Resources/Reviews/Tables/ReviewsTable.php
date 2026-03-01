<?php

namespace App\Filament\Resources\Reviews\Tables;

use Filament\Actions\Action;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // =====================
                // COVER (dari relasi publication)
                // =====================
                ImageColumn::make('cover_url')
                    ->label('')
                    ->getStateUsing(
                        fn($record) => $record->publicationVersion?->publication?->cover_url
                    )
                    ->width(44)
                    ->height(64)
                    ->extraImgAttributes([
                        'class' => 'object-cover rounded-md ring-1 ring-gray-200 dark:ring-gray-700',
                    ]),

                TextColumn::make('publicationVersion.display_label')
                    ->label('Version')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->lineClamp(2)
                    ->words(8, end: '...')
                    ->tooltip(fn(TextColumn $column): ?string => (string) $column->getState()),

                TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->lineClamp(2)
                    ->words(6, end: '...')
                    ->tooltip(fn(TextColumn $column): ?string => (string) $column->getState()),

                TextColumn::make('decision')
                    ->label('Decision')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'revision_required' => 'warning',
                        'accepted'          => 'success',
                        'rejected'          => 'danger',
                        default             => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'revision_required' => 'Revision Required',
                        'accepted'          => 'Accepted',
                        'rejected'          => 'Rejected',
                        default             => '—',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Reviewed At')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('deleted_at')
                    ->label('Deleted')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')

            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('decision')
                    ->label('Decision')
                    ->options([
                        'revision_required' => 'Revision Required',
                        'accepted'          => 'Accepted',
                        'rejected'          => 'Rejected',
                    ])
                    ->preload(),

                SelectFilter::make('reviewer_id')
                    ->label('Reviewer')
                    ->relationship('reviewer', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('publication_status')
                    ->label('Publication Status')
                    ->options([
                        'draft'             => 'Draft',
                        'submitted'         => 'Submitted',
                        'in_review'         => 'In Review',
                        'revision_required' => 'Revision Required',
                        'accepted'          => 'Accepted',
                        'rejected'          => 'Rejected',
                        'published'         => 'Published',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (blank($value)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'publicationVersion.publication',
                            fn(Builder $q) => $q->where('status', $value)
                        );
                    }),
            ])

            ->recordActions([
                ViewAction::make('preview')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->slideOver()
                    ->modalHeading('Review detail')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn($record) => view('filament.reviews.preview', ['review' => $record])),

                Action::make('download_revision')
                    ->label('Download revision')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(function ($record): bool {
                        $attachment = $record->attachments()->latest()->first();
                        return filled($attachment?->file_path);
                    })
                    ->action(function ($record) {
                        $attachment = $record->attachments()->latest()->first();

                        abort_unless($attachment && filled($attachment->file_path), 404);

                        return Storage::disk('local')->download(
                            $attachment->file_path,
                            'review-revision-' . $record->id . '.pdf'
                        );
                    }),

                EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->label('Edit')
                    ->visible(fn($record) => auth()->user()?->can('update', $record) ?? false),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => !(auth()->user()?->hasRole('reviewer'))),

                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}

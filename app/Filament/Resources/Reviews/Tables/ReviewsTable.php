<?php

namespace App\Filament\Resources\Reviews\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // =====================
                // COVER (ambil dari publication via publicationVersion)
                // UBAH relasinya jika berbeda:
                // publicationVersion.publication.cover_image_path
                // =====================
                ImageColumn::make('publicationVersion.publication.cover_image_path')
                    ->label('')
                    ->disk('public')
                    ->defaultImageUrl(url('/images/placeholder-publication.png'))
                    ->width(44)
                    ->height(64)
                    ->extraImgAttributes([
                        'class' => 'object-cover rounded-md ring-1 ring-gray-200 dark:ring-gray-700',
                    ]),

                // VERSION
                TextColumn::make('publicationVersion.display_label')
                    ->label('Version')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->lineClamp(2)
                    ->words(8, end: '...')
                    ->tooltip(fn(TextColumn $column): ?string => (string) $column->getState()),

                // REVIEWER
                TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->lineClamp(2)
                    ->words(6, end: '...')
                    ->tooltip(fn(TextColumn $column): ?string => (string) $column->getState()),

                // DECISION
                TextColumn::make('decision')
                    ->label('Decision')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'revision_required' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'revision_required' => 'Revision Required',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        default => '—',
                    })
                    ->sortable(),

                // REVIEWED AT
                TextColumn::make('created_at')
                    ->label('Reviewed At')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
                    ->label('Edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

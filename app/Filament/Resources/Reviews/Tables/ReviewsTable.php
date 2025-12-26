<?php

namespace App\Filament\Resources\Reviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('publicationVersion.display_label')
                    ->label('Version')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->searchable()
                    ->sortable(),

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

                TextColumn::make('created_at')
                    ->label('Reviewed At')
                    ->date()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make('preview')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->slideOver()
                    ->modalHeading('Review detail')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn($record) => view('filament.reviews.preview', ['review' => $record])),

                // ACTION BARU: download revisi / annotated PDF dari reviewer
                Action::make('download_revision')
                    ->label('Download Revisi')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(function ($record): bool {
                        // ambil attachment terbaru (atau first, terserah aturanmu)
                        $attachment = $record->attachments()->latest()->first();
                        return filled($attachment?->file_path);
                    })
                    ->action(function ($record) {
                        $attachment = $record->attachments()->latest()->first();

                        abort_unless($attachment && filled($attachment->file_path), 404);

                        // Jika file kamu disimpan di disk 'local' (private):
                        return Storage::disk('local')->download(
                            $attachment->file_path,
                            'review-revision-' . $record->id . '.pdf'
                        );

                        // Kalau ternyata file disimpan di disk public, pakai ini:
                        // return Storage::disk('public')->download($attachment->file_path, 'review-revision-' . $record->id . '.pdf');
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

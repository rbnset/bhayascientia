<?php

namespace App\Filament\Resources\Publications\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Storage;

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

                // UX: batasi jadi beberapa kata + tooltip biar bisa dibaca full saat hover
                Tables\Columns\TextColumn::make('overall_comment')
                    ->label('Overall comment')
                    ->words(12) // batasi beberapa kata saja. [web:113]
                    ->tooltip(fn($record) => filled($record->overall_comment) ? $record->overall_comment : null)
                    ->wrap(),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->slideOver()
                    ->modalHeading('Review detail')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function ($record): ViewContract {
                        return view('filament.reviews.preview', [
                            'review' => $record,
                        ]);
                    }),

                // ACTION BARU: download revisi (annotated PDF) dari reviewer
                Action::make('download_revision')
                    ->label('Download revisi')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(function ($record): bool {
                        $attachment = $record->attachments()->latest()->first();
                        return filled($attachment?->file_path);
                    })
                    ->action(function ($record) {
                        $attachment = $record->attachments()->latest()->first();
                        abort_unless($attachment && filled($attachment->file_path), 404);

                        // Kalau mau dibatasi hanya author/reviewer tertentu, taruh policy di sini:
                        // abort_unless(auth()->user()->can('downloadReviewAttachment', $record));

                        return Storage::disk('local')->download(
                            $attachment->file_path,
                            'review-revision-' . $record->id . '.pdf'
                        );

                        // Jika file ada di public:
                        // return Storage::disk('public')->download($attachment->file_path, 'review-revision-' . $record->id . '.pdf');
                    }),
            ])
            ->headerActions([])
            ->bulkActions([]);
    }
}

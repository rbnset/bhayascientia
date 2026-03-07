<?php

namespace App\Filament\Resources\Publications\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
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
            ->recordClasses(fn($record) => match ($record->decision) {
                'rejected'          => 'bg-red-50 dark:bg-red-950/20',
                'accepted'          => 'bg-emerald-50 dark:bg-emerald-950/20',
                'revision_required' => 'bg-amber-50 dark:bg-amber-950/20',
                default             => null,
            })
            ->modifyQueryUsing(fn($query) => $query->with(['attachments', 'notes', 'reviewer', 'publicationVersion']))
            ->columns([

                // ── Versi naskah yang direview ─────────────────────────────
                TextColumn::make('publicationVersion.version_number')
                    ->label('Version')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn($state) => $state ? 'v' . $state : '—'),

                // ── Nama reviewer ──────────────────────────────────────────
                TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->description(fn($record) => $record->reviewer?->email),

                // ── Keputusan review ───────────────────────────────────────
                TextColumn::make('decision')
                    ->label('Decision')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'accepted'          => 'success',
                        'revision_required' => 'warning',
                        'rejected'          => 'danger',
                        default             => 'gray',
                    })
                    ->icon(fn(?string $state): string => match ($state) {
                        'accepted'          => 'heroicon-o-check-circle',
                        'revision_required' => 'heroicon-o-exclamation-circle',
                        'rejected'          => 'heroicon-o-x-circle',
                        default             => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(
                        fn(?string $state) => $state
                            ? str($state)->headline()
                            : 'Pending'
                    ),

                // ── Tanggal review (WIB) ───────────────────────────────────
                TextColumn::make('created_at')
                    ->label('Reviewed At')
                    ->sortable()
                    ->formatStateUsing(
                        fn($state) => $state
                            ? \Carbon\Carbon::parse($state)
                            ->setTimezone('Asia/Jakarta')
                            ->translatedFormat('d M Y, H:i') . ' WIB'
                            : '—'
                    )
                    ->tooltip(
                        fn($record) => \Carbon\Carbon::parse($record->created_at)
                            ->setTimezone('Asia/Jakarta')
                            ->diffForHumans()
                    ),

                // ── Ringkasan komentar ─────────────────────────────────────
                TextColumn::make('overall_comment')
                    ->label('Overall Comment')
                    ->words(12)
                    ->wrap()
                    ->placeholder('No comment')
                    ->tooltip(
                        fn($record) => filled($record->overall_comment)
                            ? $record->overall_comment
                            : null
                    ),

                // ── Indikator ada attachment atau tidak ────────────────────
                Tables\Columns\IconColumn::make('has_attachment')
                    ->label('Attachment')
                    ->state(fn($record) => $record->attachments->isNotEmpty())
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('primary')
                    ->falseColor('gray')
                    ->tooltip(
                        fn($record) => $record->attachments->isNotEmpty()
                            ? $record->attachments->count() . ' file(s) attached'
                            : 'No attachment'
                    ),

                // ── Jumlah catatan review ──────────────────────────────────
                TextColumn::make('notes_count')
                    ->label('Notes')
                    ->state(fn($record) => $record->notes->count())
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'warning' : 'gray')
                    ->formatStateUsing(fn($state) => $state > 0 ? $state . ' note' . ($state > 1 ? 's' : '') : '—')
                    ->tooltip(
                        fn($record) => $record->notes->count() > 0
                            ? 'Click Detail to read reviewer notes'
                            : 'No notes'
                    ),
            ])

            ->actions([
                // ── Detail: hanya overall_comment + notes per section ──────
                ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('info')
                    ->slideOver()
                    ->modalHeading(
                        fn($record) => 'Review — v' .
                            ($record->publicationVersion?->version_number ?? '?') .
                            '  ·  ' . ($record->reviewer?->name ?? 'Unknown')
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn($record): ViewContract => view(
                        'filament.reviews.preview',
                        ['review' => $record]
                    )),

                // ── Download: langsung download attachment, tanpa modal ─────
                Action::make('download_revision')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->tooltip('Download reviewer attachment')
                    ->visible(fn($record): bool => $record->attachments->isNotEmpty())
                    ->action(function ($record) {
                        $attachment = $record->attachments->sortByDesc('created_at')->first();

                        abort_unless(
                            $attachment && filled($attachment->file_path),
                            404,
                            'Attachment not found.'
                        );

                        $filename = 'review-v' .
                            ($record->publicationVersion?->version_number ?? $record->id) .
                            '-' . str($record->reviewer?->name ?? 'reviewer')->slug() .
                            '.pdf';

                        return Storage::disk('local')->download(
                            $attachment->file_path,
                            $filename
                        );
                    }),
            ])

            ->headerActions([])
            ->bulkActions([])

            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->emptyStateHeading('No reviews yet')
            ->emptyStateDescription('Reviews will appear here once a reviewer submits feedback.');
    }
}

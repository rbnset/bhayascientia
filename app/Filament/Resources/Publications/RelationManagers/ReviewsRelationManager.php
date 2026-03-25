<?php

namespace App\Filament\Resources\Publications\RelationManagers;

use App\Filament\Resources\Reviews\ReviewResource;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Reviews & Feedback';

    protected function isAuthor(): bool
    {
        return (bool) auth()->user()?->hasRole('author');
    }

    protected function isReviewer(): bool
    {
        return (bool) auth()->user()?->hasRole('reviewer');
    }

    protected function getTableQuery(): Builder
    {
        $publicationId = $this->ownerRecord->id;

        return \App\Models\Review::query()
            ->where(function ($query) use ($publicationId) {

                // ✅ review opini (langsung ke publication)
                $query->where('publication_id', $publicationId)

                    // ✅ review via version
                    ->orWhereHas('publicationVersion', function ($q) use ($publicationId) {
                        $q->where('publication_id', $publicationId);
                    });
            });
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            // ✅ WAJIB: load kedua relasi (opini & version)
            ->modifyQueryUsing(fn(Builder $query) => $query->with([
                'attachments',
                'notes',
                'reviewer',
                'publicationVersion.publication',
                'publication',
            ]))

            ->recordClasses(fn($record) => match ($record->decision) {
                'rejected'          => 'bg-red-50 dark:bg-red-950/20',
                'accepted'          => 'bg-emerald-50 dark:bg-emerald-950/20',
                'revision_required' => 'bg-amber-50 dark:bg-amber-950/20',
                default             => null,
            })

            ->columns([

                // ✅ FIX: Support opini & version
                TextColumn::make('publication_label')
                    ->label('Publication / Version')
                    ->getStateUsing(function ($record) {

                        // OPINI (tanpa version)
                        if (is_null($record->publication_version_id)) {
                            $title = $record->publication?->title ?? '—';
                            return \Illuminate\Support\Str::words($title, 6, '...') . ' — Opini';
                        }

                        // VERSION
                        return 'v' . ($record->publicationVersion?->version_number ?? '?');
                    })
                    ->badge()
                    ->color('gray')
                    ->wrap()
                    ->tooltip(function ($record) {
                        return is_null($record->publication_version_id)
                            ? ($record->publication?->title ?? '—')
                            : ('Version ' . ($record->publicationVersion?->version_number ?? '?'));
                    }),

                TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->description(fn($record) => $record->reviewer?->email),

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
                        default             => 'heroicon-o-clock',
                    })
                    ->formatStateUsing(
                        fn(?string $state) => $state ? str($state)->headline() : 'Pending'
                    ),

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

                TextColumn::make('overall_comment')
                    ->label('Overall Comment')
                    ->words(12)
                    ->wrap()
                    ->placeholder('No comment')
                    ->formatStateUsing(
                        fn(?string $state) => filled($state)
                            ? strip_tags($state)
                            : null
                    )
                    ->tooltip(
                        fn($record) => filled($record->overall_comment)
                            ? strip_tags($record->overall_comment)
                            : null
                    ),

                Tables\Columns\IconColumn::make('has_attachment')
                    ->label('Attachment')
                    ->state(fn($record) => $record->attachments->isNotEmpty())
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('primary')
                    ->falseColor('gray'),

                TextColumn::make('notes_count')
                    ->label('Notes')
                    ->state(fn($record) => $record->notes->count())
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'warning' : 'gray')
                    ->formatStateUsing(
                        fn($state) => $state > 0 ? $state : '—'
                    ),
            ])

            ->actions([

                Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('info')
                    ->url(function ($record): string {

                        if ($this->isAuthor()) {
                            return ReviewResource::getUrl('view', ['record' => $record->id]);
                        }

                        return ReviewResource::getUrl('edit', ['record' => $record->id]);
                    }),

                Action::make('download_revision')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn($record) => $record->attachments->isNotEmpty())
                    ->action(function ($record) {

                        $attachment = $record->attachments->sortByDesc('created_at')->first();

                        abort_unless($attachment && filled($attachment->file_path), 404);

                        // ✅ FIX: support opini
                        $versionLabel = is_null($record->publication_version_id)
                            ? 'opini'
                            : ('v' . ($record->publicationVersion?->version_number ?? $record->id));

                        $filename = 'review-' . $versionLabel . '-' .
                            str($record->reviewer?->name ?? 'reviewer')->slug() . '.pdf';

                        return Storage::disk('local')->download(
                            $attachment->file_path,
                            $filename
                        );
                    }),
            ])

            ->headerActions([])
            ->bulkActions([])

            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->emptyStateHeading('Belum ada review')
            ->emptyStateDescription('Review akan muncul di sini setelah reviewer mengirimkan feedback.');
    }
}

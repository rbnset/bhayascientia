<?php

namespace App\Filament\Resources\Reviews\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
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

                // ── Cover publikasi ──
                // ✅ FIXED: support opini — fallback ke publication langsung jika version null
                ImageColumn::make('cover_url')
                    ->label('')
                    ->getStateUsing(function ($record) {
                        // Opini tanpa manuskrip: ambil langsung dari publication
                        if (is_null($record->publication_version_id)) {
                            return $record->publication?->cover_url;
                        }
                        // Tipe lain: via publicationVersion
                        return $record->publicationVersion?->publication?->cover_url;
                    })
                    ->width(44)
                    ->height(64)
                    ->extraImgAttributes([
                        'class' => 'object-cover rounded-md ring-1 ring-gray-200 dark:ring-gray-700 shadow-sm',
                    ])
                    ->defaultImageUrl(fn() => asset('images/publication-placeholder.png')),

                // ── Versi publikasi ──
                // ✅ FIXED: support opini — tampilkan judul publikasi + label "Opini" atau "v{n}"
                TextColumn::make('publication_label')
                    ->label('Publication / Version')
                    ->getStateUsing(function ($record) {
                        // Opini tanpa manuskrip: ambil langsung dari publication
                        if (is_null($record->publication_version_id)) {
                            $pub = $record->publication;
                            if (!$pub) return '—';
                            $title   = \Illuminate\Support\Str::words($pub->title ?? '', 8, '...');
                            return $title . ' — Opini';
                        }
                        // Tipe lain: gunakan display_label dari publicationVersion
                        return $record->publicationVersion?->display_label ?? '—';
                    })
                    ->sortable(query: function (Builder $query, string $direction) {
                        // Sort by publication title untuk konsistensi
                        $query->leftJoin('publication_versions as pv_sort', 'pv_sort.id', '=', 'reviews.publication_version_id')
                            ->leftJoin('publications as pub_sort', function ($join) {
                                $join->on('pub_sort.id', '=', 'pv_sort.publication_id')
                                    ->orOn('pub_sort.id', '=', 'reviews.publication_id');
                            })
                            ->orderBy('pub_sort.title', $direction);
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('publicationVersion.publication', fn($q2) => $q2->where('title', 'like', "%{$search}%"))
                                ->orWhereHas('publication', fn($q2) => $q2->where('title', 'like', "%{$search}%"));
                        });
                    })
                    ->wrap()
                    ->lineClamp(2)
                    ->tooltip(
                        fn($record) => is_null($record->publication_version_id)
                            ? ($record->publication?->title ?? '—')
                            : ($record->publicationVersion?->display_label ?? '—')
                    )
                    ->description(function ($record) {
                        // ✅ Tampilkan tipe publikasi — support opini
                        if (is_null($record->publication_version_id)) {
                            return $record->publication?->publicationType?->name ?? '—';
                        }
                        return $record->publicationVersion?->publication?->publicationType?->name ?? '—';
                    }),

                // ── Reviewer ───────────────────────────────────────────────
                TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->lineClamp(2)
                    ->words(6, end: '...')
                    ->tooltip(fn(TextColumn $column): ?string => (string) $column->getState())
                    ->description(fn($record) => $record->reviewer?->email)
                    ->placeholder('—'),

                // ── Decision ───────────────────────────────────────────────
                TextColumn::make('decision')
                    ->label('Decision')
                    ->badge()
                    ->icon(fn(?string $state): string => match ($state) {
                        'revision_required' => 'heroicon-o-exclamation-circle',
                        'accepted'          => 'heroicon-o-check-circle',
                        'rejected'          => 'heroicon-o-x-circle',
                        default             => 'heroicon-o-clock',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'revision_required' => 'warning',
                        'accepted'          => 'success',
                        'rejected'          => 'danger',
                        default             => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'revision_required' => 'Revision Required',
                        'accepted'          => 'Accepted',
                        'rejected'          => 'Rejected',
                        default             => 'Pending',
                    })
                    ->sortable(),

                // ── Status publikasi terkait ───────────────────────────────
                // ✅ FIXED: support opini — baca status dari publication langsung jika version null
                TextColumn::make('pub_status')
                    ->label('Pub. Status')
                    ->getStateUsing(function ($record) {
                        if (is_null($record->publication_version_id)) {
                            return $record->publication?->status ?? null;
                        }
                        return $record->publicationVersion?->publication?->status ?? null;
                    })
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'draft'             => 'gray',
                        'submitted'         => 'warning',
                        'in_review'         => 'info',
                        'revision_required' => 'danger',
                        'accepted'          => 'success',
                        'published'         => 'success',
                        'rejected'          => 'danger',
                        default             => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => $state ? str($state)->headline() : '—')
                    ->toggleable(),

                // ── Jumlah notes ───────────────────────────────────────────
                TextColumn::make('notes_count')
                    ->label('Notes')
                    ->state(fn($record) => $record->notes->count())
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'warning' : 'gray')
                    ->formatStateUsing(fn($state) => $state > 0 ? $state : '—')
                    ->tooltip(
                        fn($record) => $record->notes->count() > 0
                            ? 'Has reviewer notes'
                            : 'No notes'
                    )
                    ->toggleable(),

                // ── Ada attachment? ────────────────────────────────────────
                IconColumn::make('has_attachment')
                    ->label('File')
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
                    )
                    ->toggleable(),

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

                TextColumn::make('deleted_at')
                    ->label('Deleted')
                    ->formatStateUsing(
                        fn($state) => $state
                            ? \Carbon\Carbon::parse($state)
                            ->setTimezone('Asia/Jakarta')
                            ->translatedFormat('d M Y, H:i') . ' WIB'
                            : '—'
                    )
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn($query) => $query->with([
                'attachments',
                'notes',
                'reviewer',
                // ✅ FIXED: eager load keduanya — via version (non-opini) dan langsung (opini)
                'publicationVersion.publication.publicationType',
                'publication.publicationType',
            ]))
            ->striped()
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()

            // ── Row warna sesuai decision ──────────────────────────────────
            ->recordClasses(fn($record) => match ($record->decision) {
                'rejected'          => 'bg-red-50/50 dark:bg-red-950/10',
                'accepted'          => 'bg-emerald-50/50 dark:bg-emerald-950/10',
                'revision_required' => 'bg-amber-50/50 dark:bg-amber-950/10',
                default             => null,
            })

            // ── Filters ────────────────────────────────────────────────────
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
                        if (blank($value)) return $query;

                        // ✅ FIXED: filter status untuk kedua tipe (dengan & tanpa versi)
                        return $query->where(function ($q) use ($value) {
                            $q->whereHas(
                                'publicationVersion.publication',
                                fn(Builder $q2) => $q2->where('status', $value)
                            )->orWhereHas(
                                'publication',
                                fn(Builder $q2) => $q2->where('status', $value)
                            );
                        });
                    }),

                // Filter: range tanggal review
                Filter::make('reviewed_range')
                    ->label('Reviewed Date Range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('reviewed_from')
                            ->label('From')
                            ->native(false),
                        \Filament\Forms\Components\DatePicker::make('reviewed_until')
                            ->label('Until')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['reviewed_from'], fn($q, $v) => $q->whereDate('created_at', '>=', $v))
                            ->when($data['reviewed_until'], fn($q, $v) => $q->whereDate('created_at', '<=', $v));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['reviewed_from'] ?? null)
                            $indicators[] = 'From: ' . \Carbon\Carbon::parse($data['reviewed_from'])->translatedFormat('d M Y');
                        if ($data['reviewed_until'] ?? null)
                            $indicators[] = 'Until: ' . \Carbon\Carbon::parse($data['reviewed_until'])->translatedFormat('d M Y');
                        return $indicators;
                    }),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filters')
                    ->icon('heroicon-o-funnel'),
            )

            // ── Record Actions ─────────────────────────────────────────────
            ->recordActions([
                // Detail: overall_comment + notes (tanpa attachment)
                ViewAction::make('preview')
                    ->label('Detail')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('info')
                    ->slideOver()
                    ->modalHeading(function ($record) {
                        // ✅ FIXED: support opini — label versi fleksibel
                        $versionLabel = is_null($record->publication_version_id)
                            ? 'Opini'
                            : ('v' . ($record->publicationVersion?->version_number ?? '?'));
                        return 'Review — ' . $versionLabel . '  ·  ' . ($record->reviewer?->name ?? 'Unknown');
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn($record) => view(
                        'filament.reviews.preview',
                        ['review' => $record->load(['notes', 'attachments', 'reviewer', 'publicationVersion', 'publication'])]
                    )),

                // Download: langsung download attachment
                Action::make('download_revision')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->tooltip('Download revision attachment')
                    ->visible(fn($record): bool => $record->attachments->isNotEmpty())
                    ->action(function ($record) {
                        $attachment = $record->attachments->sortByDesc('created_at')->first();

                        abort_unless($attachment && filled($attachment->file_path), 404);

                        // ✅ FIXED: label filename support opini
                        $versionLabel = is_null($record->publication_version_id)
                            ? 'opini'
                            : ('v' . ($record->publicationVersion?->version_number ?? $record->id));

                        $filename = 'review-' . $versionLabel .
                            '-' . str($record->reviewer?->name ?? 'reviewer')->slug() .
                            '.pdf';

                        return Storage::disk('local')->download(
                            $attachment->file_path,
                            $filename
                        );
                    }),

                EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->label('Edit')
                    ->visible(fn($record) => auth()->user()?->can('update', $record) ?? false),
            ])

            // ── Toolbar / Bulk Actions ─────────────────────────────────────
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => ! auth()->user()?->hasRole('reviewer')),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])

            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->emptyStateHeading('No reviews found')
            ->emptyStateDescription('Reviews will appear here once reviewers submit their feedback.');
    }
}

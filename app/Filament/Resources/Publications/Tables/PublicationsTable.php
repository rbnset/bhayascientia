<?php

namespace App\Filament\Resources\Publications\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PublicationsTable
{
    /**
     * Status yang mengunci aksi edit & delete untuk role author.
     */
    private const AUTHOR_LOCKED_STATUSES = [
        'in_review',
        'accepted',
        'rejected',
        'published',
    ];

    /**
     * Urutan transisi status yang diizinkan (state machine sederhana).
     * Key = status saat ini, Value = status yang boleh dipilih.
     */
    private const STATUS_TRANSITIONS = [
        'draft'             => ['draft', 'submitted'],
        'submitted'         => ['submitted', 'in_review', 'revision_required', 'rejected'],
        'in_review'         => ['in_review', 'revision_required', 'accepted', 'rejected'],
        'revision_required' => ['revision_required', 'submitted', 'rejected'],
        'accepted'          => ['accepted', 'published', 'rejected'],
        'rejected'          => ['rejected', 'draft'],
        'published'         => ['published'],
    ];

    /**
     * Label & warna badge per status.
     */
    private const STATUS_META = [
        'draft'             => ['label' => 'Draft',             'color' => 'gray',    'icon' => 'heroicon-o-pencil'],
        'submitted'         => ['label' => 'Submitted',         'color' => 'warning', 'icon' => 'heroicon-o-paper-airplane'],
        'in_review'         => ['label' => 'In Review',         'color' => 'info',    'icon' => 'heroicon-o-magnifying-glass'],
        'revision_required' => ['label' => 'Revision Required', 'color' => 'danger',  'icon' => 'heroicon-o-exclamation-circle'],
        'accepted'          => ['label' => 'Accepted',          'color' => 'success', 'icon' => 'heroicon-o-check-circle'],
        'published'         => ['label' => 'Published',         'color' => 'success', 'icon' => 'heroicon-o-globe-alt'],
        'rejected'          => ['label' => 'Rejected',          'color' => 'danger',  'icon' => 'heroicon-o-x-circle'],
    ];

    /**
     * Apakah record ini terkunci untuk author yang sedang login?
     */
    private static function isLockedForAuthor(mixed $record): bool
    {
        return auth()->user()?->hasRole('author')
            && in_array($record->status, self::AUTHOR_LOCKED_STATUSES, true);
    }

    /**
     * Apakah user boleh mengubah status? (editor/admin/reviewer saja)
     */
    private static function canChangeStatus(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'editor', 'reviewer']) ?? false;
    }

    /**
     * Ambil options status yang diperbolehkan dari status saat ini.
     */
    private static function allowedStatusOptions(string $currentStatus): array
    {
        $allowed = self::STATUS_TRANSITIONS[$currentStatus] ?? array_keys(self::STATUS_META);

        return collect(self::STATUS_META)
            ->only($allowed)
            ->map(fn($meta) => $meta['label'])
            ->toArray();
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // ── Cover ──────────────────────────────────────────────────
                ImageColumn::make('cover_url')
                    ->label('')
                    ->width(44)
                    ->height(64)
                    ->extraImgAttributes([
                        'class' => 'object-cover rounded-md ring-1 ring-gray-200 dark:ring-gray-700 shadow-sm',
                    ])
                    ->defaultImageUrl(fn() => asset('images/publication-placeholder.png')),

                // ── Judul + tipe publikasi ─────────────────────────────────
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap()
                    ->lineClamp(3)
                    ->words(14, end: '...')
                    ->tooltip(fn(TextColumn $column): ?string => (string) $column->getState())
                    ->description(fn($record) => $record->publicationType?->name),

                // ── Authors ────────────────────────────────────────────────
                TextColumn::make('authors_list')
                    ->label('Authors')
                    ->state(function ($record) {
                        return $record->authors
                            ->sortBy('pivot.order')
                            ->pluck('name')
                            ->filter()
                            ->implode(', ');
                    })
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('authors', function ($q) use ($search) {
                            $q->where('authors.name', 'like', "%{$search}%")
                                ->orWhere('authors.email', 'like', "%{$search}%")
                                ->orWhereHas(
                                    'user',
                                    fn($u) =>
                                    $u->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%")
                                );
                        });
                    })
                    ->tooltip(function ($record) {
                        return $record->authors
                            ->sortBy('pivot.order')
                            ->map(fn($a) => $a->name . ($a->affiliation ? " ({$a->affiliation})" : ''))
                            ->implode("\n");
                    })
                    ->toggleable(),

                // ── Kategori ───────────────────────────────────────────────
                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge()
                    ->separator(', ')
                    ->color('primary')
                    ->limitList(3)
                    ->listWithLineBreaks()
                    ->toggleable(),

                // ── Metode ─────────────────────────────────────────────────
                TextColumn::make('method.name')
                    ->label('Method')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),

                // ── Status ─────────────────────────────────────────────────
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => self::STATUS_META[$state]['color'] ?? 'gray')
                    ->icon(fn(string $state): string => self::STATUS_META[$state]['icon'] ?? 'heroicon-o-question-mark-circle')
                    ->formatStateUsing(fn($state) => self::STATUS_META[$state]['label'] ?? str($state)->headline())
                    ->sortable(),

                // ── Tanggal publikasi ──────────────────────────────────────
                TextColumn::make('published_at')
                    ->label('Published')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Deleted')
                    ->dateTime()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')

            // ── FILTERS ────────────────────────────────────────────────────
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(
                        collect(self::STATUS_META)->map(fn($m) => $m['label'])->toArray()
                    )
                    ->preload(),

                SelectFilter::make('publication_type_id')
                    ->label('Publication Type')
                    ->relationship('publicationType', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('method_id')
                    ->label('Method')
                    ->relationship('method', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('categories')
                    ->label('Category')
                    ->relationship('categories', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('author')
                    ->label('Author')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('author_name')
                            ->label('Author Name')
                            ->placeholder('Search by author name...')
                            ->prefixIcon('heroicon-o-user'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (blank($data['author_name'])) return $query;

                        return $query->whereHas('authors', function ($q) use ($data) {
                            $search = $data['author_name'];
                            $q->where('authors.name', 'like', "%{$search}%")
                                ->orWhereHas(
                                    'user',
                                    fn($u) => $u->where('name', 'like', "%{$search}%")
                                );
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (blank($data['author_name'])) return null;
                        return 'Author: ' . $data['author_name'];
                    }),

                // Filter: Range tanggal publikasi
                Filter::make('published_range')
                    ->label('Published Date Range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('published_from')
                            ->label('From')
                            ->native(false),
                        \Filament\Forms\Components\DatePicker::make('published_until')
                            ->label('Until')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['published_from'], fn($q, $v) => $q->whereDate('published_at', '>=', $v))
                            ->when($data['published_until'], fn($q, $v) => $q->whereDate('published_at', '<=', $v));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['published_from'] ?? null) {
                            $indicators[] = 'Published from: ' . \Carbon\Carbon::parse($data['published_from'])->toFormattedDateString();
                        }
                        if ($data['published_until'] ?? null) {
                            $indicators[] = 'Published until: ' . \Carbon\Carbon::parse($data['published_until'])->toFormattedDateString();
                        }
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

            // ── RECORD ACTIONS ─────────────────────────────────────────────
            ->recordActions([
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->label('View')
                    ->slideOver()
                    ->color('gray'),

                EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->label('Edit')
                    ->visible(fn($record) => ! self::isLockedForAuthor($record)),

                // ── Change Status Action ───────────────────────────────────
                Action::make('changeStatus')
                    ->label('Change Status')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('warning')
                    ->visible(fn($record) => static::canChangeStatus())
                    ->fillForm(fn($record) => ['status' => $record->status])
                    ->form(fn($record) => [
                        // Info status saat ini
                        \Filament\Forms\Components\Placeholder::make('current_status_info')
                            ->label('Current Status')
                            ->content(
                                fn() => new \Illuminate\Support\HtmlString(
                                    '<span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">'
                                        . '<span class="w-2 h-2 bg-current rounded-full opacity-60"></span>'
                                        . (self::STATUS_META[$record->status]['label'] ?? str($record->status)->headline())
                                        . '</span>'
                                )
                            ),

                        Select::make('status')
                            ->label('New Status')
                            ->options(static::allowedStatusOptions($record->status))
                            ->default($record->status)
                            ->required()
                            ->native(false)
                            ->helperText('Only valid transitions are shown based on the current status.'),

                        Textarea::make('status_note')
                            ->label('Note / Reason (optional)')
                            ->placeholder('e.g. Revision needed on methodology section...')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->action(function ($record, array $data): void {
                        $oldStatus = $record->status;
                        $newStatus = $data['status'];

                        if ($oldStatus === $newStatus) {
                            Notification::make()
                                ->title('No change')
                                ->body('Status is already ' . (self::STATUS_META[$newStatus]['label'] ?? $newStatus) . '.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $record->update([
                            'status'      => $newStatus,
                            // Simpan note jika ada kolom status_notes di tabel
                            // 'status_notes' => $data['status_note'] ?? null,
                        ]);

                        // Set published_at otomatis jika dipublish
                        if ($newStatus === 'published' && ! $record->published_at) {
                            $record->update(['published_at' => now()]);
                        }

                        // Activity log (opsional, butuh spatie/laravel-activitylog)
                        // activity()->performedOn($record)->log("Status changed from {$oldStatus} to {$newStatus}");

                        Notification::make()
                            ->title('Status Updated')
                            ->body(
                                'Changed from <strong>'
                                    . (self::STATUS_META[$oldStatus]['label'] ?? $oldStatus)
                                    . '</strong> → <strong>'
                                    . (self::STATUS_META[$newStatus]['label'] ?? $newStatus)
                                    . '</strong>'
                            )
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Change Publication Status')
                    ->modalDescription('Select the new status for this publication. Only valid transitions are available.')
                    ->modalSubmitActionLabel('Update Status')
                    ->modalCancelActionLabel('Cancel')
                    ->modalWidth('lg'),

                DeleteAction::make()
                    ->visible(fn($record) => ! self::isLockedForAuthor($record))
                    ->requiresConfirmation()
                    ->modalHeading('Delete Publication')
                    ->modalDescription('Are you sure? This action can be undone from the trash.')
                    ->modalSubmitActionLabel('Yes, delete it'),
            ])

            // ── TOOLBAR / BULK ACTIONS ─────────────────────────────────────
            ->toolbarActions([
                BulkActionGroup::make([
                    // Bulk change status
                    \Filament\Actions\BulkAction::make('bulkChangeStatus')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path-rounded-square')
                        ->color('warning')
                        ->visible(fn() => static::canChangeStatus())
                        ->form([
                            Select::make('status')
                                ->label('New Status')
                                ->options(
                                    collect(self::STATUS_META)->map(fn($m) => $m['label'])->toArray()
                                )
                                ->required()
                                ->native(false),

                            Textarea::make('status_note')
                                ->label('Note / Reason (optional)')
                                ->placeholder('Reason for bulk status change...')
                                ->rows(2),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
                            $records->each(fn($record) => $record->update(['status' => $data['status']]));

                            Notification::make()
                                ->title($records->count() . ' publications updated')
                                ->body('Status changed to: ' . (self::STATUS_META[$data['status']]['label'] ?? $data['status']))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Change Status')
                        ->modalDescription('This will update the status for ALL selected publications.')
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])

            // ── TABLE UX ENHANCEMENTS ──────────────────────────────────────
            ->striped()                          // baris bergantian warna
            ->paginated([10, 25, 50, 100])       // pilihan items per page
            ->defaultPaginationPageOption(25)
            ->poll(null)                         // disable auto-refresh (aktifkan: '30s')
            ->deferLoading()                     // lazy load saat pertama buka
            ->persistSearchInSession()           // simpan search di session
            ->persistFiltersInSession()          // simpan filter di session
            ->persistSortInSession()             // simpan sort di session
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('No publications found')
            ->emptyStateDescription('Try adjusting your filters or create a new publication.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create Publication')
                    ->icon('heroicon-o-plus')
                    ->url(fn() => \App\Filament\Resources\Publications\PublicationResource::getUrl('create'))
                    ->button(),
            ]);
    }
}

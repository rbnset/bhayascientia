<?php

namespace App\Filament\Resources\Publications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PublicationsTable
{
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
                        'class' => 'object-cover rounded-md ring-1 ring-gray-200 dark:ring-gray-700',
                    ]),

                // ── Judul + tipe publikasi ──────────────────────────────────
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

                // ── Authors ─────────────────────────────────────────────────
                // ✅ FIXED: authors.name sekarang resolved via accessor
                // (jika user_id ada → ambil dari users.name, jika tidak → authors.name)
                // Kolom ->state() dipakai agar tidak langsung raw dari DB
                TextColumn::make('authors_list')
                    ->label('Authors')
                    ->state(function ($record) {
                        // ✅ Pakai accessor name yang sudah resolved di Author model
                        return $record->authors
                            ->sortBy('pivot.order')
                            ->pluck('name') // accessor name sudah resolved
                            ->filter()
                            ->implode(', ');
                    })
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search) {
                        // ✅ Search ke authors.name DAN users.name
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
                        // Tooltip tampilkan nama + affiliasi
                        return $record->authors
                            ->sortBy('pivot.order')
                            ->map(fn($a) => $a->name . ($a->affiliation ? " ({$a->affiliation})" : ''))
                            ->implode("\n");
                    }),

                // ── Kategori ────────────────────────────────────────────────
                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge()
                    ->separator(', ')
                    ->color('primary')
                    ->limitList(3)
                    ->listWithLineBreaks(),

                // ── Metode ──────────────────────────────────────────────────
                TextColumn::make('method.name')
                    ->label('Method')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),

                // ── Status ──────────────────────────────────────────────────
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft'             => 'gray',
                        'submitted'         => 'warning',
                        'in_review'         => 'info',
                        'revision_required' => 'danger',
                        'accepted'          => 'success',
                        'published'         => 'success',
                        'rejected'          => 'danger',
                        default             => 'gray',
                    })
                    ->formatStateUsing(fn($state) => str($state)->headline())
                    ->sortable(),

                // ── Tanggal publikasi ───────────────────────────────────────
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
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'             => 'Draft',
                        'submitted'         => 'Submitted',
                        'in_review'         => 'In Review',
                        'revision_required' => 'Revision Required',
                        'accepted'          => 'Accepted',
                        'rejected'          => 'Rejected',
                        'published'         => 'Published',
                    ])
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

                // ✅ BARU: Filter berdasarkan kategori
                SelectFilter::make('categories')
                    ->label('Category')
                    ->relationship('categories', 'name')
                    ->searchable()
                    ->preload(),

                // ✅ BARU: Filter berdasarkan author
                // Cari di authors.name DAN users.name
                Filter::make('author')
                    ->label('Author')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('author_name')
                            ->label('Nama Author')
                            ->placeholder('Cari nama author...'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (blank($data['author_name'])) return $query;

                        return $query->whereHas('authors', function ($q) use ($data) {
                            $search = $data['author_name'];
                            $q->where('authors.name', 'like', "%{$search}%")
                                ->orWhereHas(
                                    'user',
                                    fn($u) =>
                                    $u->where('name', 'like', "%{$search}%")
                                );
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (blank($data['author_name'])) return null;
                        return 'Author: ' . $data['author_name'];
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->label('View')
                    ->slideOver(),

                EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->label('Edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}

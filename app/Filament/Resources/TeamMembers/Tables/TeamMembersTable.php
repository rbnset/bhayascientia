<?php

namespace App\Filament\Resources\TeamMembers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class TeamMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // ═══════════════════════════════════════════════════════════════
            // QUERY OPTIMIZATION
            // ═══════════════════════════════════════════════════════════════
            ->modifyQueryUsing(fn(Builder $query) => $query->select([
                'id',
                'name',
                'title',
                'department',
                'level',
                'photo',
                'email',
                'member_count',
                'order',
                'is_active',
                'created_at',
                'updated_at',
            ]))

            // ═══════════════════════════════════════════════════════════════
            // EMPTY STATE
            // ═══════════════════════════════════════════════════════════════
            ->emptyStateIcon(Heroicon::OutlinedUsers)
            ->emptyStateHeading('Belum Ada Anggota Tim')
            ->emptyStateDescription('Mulai tambahkan anggota tim untuk ditampilkan di halaman organisasi.')
            ->emptyStateActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Tambah Anggota Tim')
                    ->icon(Heroicon::OutlinedUserPlus),
            ])

            // ═══════════════════════════════════════════════════════════════
            // COLUMNS
            // ═══════════════════════════════════════════════════════════════
            ->columns([

                // ─── Foto ────────────────────────────────────────────────
                ImageColumn::make('photo_url')
                    ->label('')
                    ->circular()
                    ->size(48)
                    ->defaultImageUrl(
                        fn($record): string =>
                        'https://ui-avatars.com/api/?name='
                            . urlencode($record->name ?? 'NN')
                            . '&size=80&background=FFF7F2&color=FF6B18&bold=true'
                    ),

                // ─── Identitas ───────────────────────────────────────────
                TextColumn::make('name')
                    ->label('Anggota')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn($record): string => $record->title ?? '')
                    ->wrap(),

                // ─── Level ───────────────────────────────────────────────
                TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'leadership' => 'danger',
                        'management' => 'warning',
                        'department' => 'success',
                        default      => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'leadership' => 'heroicon-o-sparkles',
                        'management' => 'heroicon-o-building-office',
                        'department' => 'heroicon-o-user-group',
                        default      => 'heroicon-o-tag',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'leadership' => 'Leadership',
                        'management' => 'Management',
                        'department' => 'Department',
                        default      => ucfirst($state),
                    })
                    ->searchable()
                    ->sortable(),

                // ─── Departemen ──────────────────────────────────────────
                TextColumn::make('department')
                    ->label('Departemen')
                    ->icon(Heroicon::OutlinedBuildingOffice2)
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),

                // ─── Email ───────────────────────────────────────────────
                TextColumn::make('email')
                    ->label('Email')
                    ->copyable()
                    ->copyMessage('Email disalin!')
                    ->copyMessageDuration(1500)
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),

                // ─── Jumlah Anggota ───────────────────────────────────────
                TextColumn::make('member_count')
                    ->label('Anggota')
                    ->numeric()
                    ->sortable()
                    ->icon(Heroicon::OutlinedUsers)
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->placeholder('0')
                    ->toggleable(),

                // ─── Urutan ──────────────────────────────────────────────
                TextColumn::make('order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                // ─── Status ──────────────────────────────────────────────
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter()
                    ->sortable(),

                // ─── Timestamps ──────────────────────────────────────────
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->sortable()
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->color('gray')
                    ->since()
                    ->tooltip(fn($record): string => $record->created_at?->translatedFormat('d F Y, H:i') ?? '')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->sortable()
                    ->icon(Heroicon::OutlinedClock)
                    ->color('gray')
                    ->since()
                    ->tooltip(fn($record): string => $record->updated_at?->translatedFormat('d F Y, H:i') ?? '')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // ═══════════════════════════════════════════════════════════════
            // DEFAULT SORT
            // ═══════════════════════════════════════════════════════════════
            ->defaultSort('order', 'asc')

            // ═══════════════════════════════════════════════════════════════
            // FILTERS
            // ═══════════════════════════════════════════════════════════════
            ->filters([
                SelectFilter::make('level')
                    ->label('Level')
                    ->options([
                        'leadership' => 'Leadership',
                        'management' => 'Management',
                        'department' => 'Department',
                    ])
                    ->native(false)
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Status Tampil')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->placeholder('Semua')
                    ->native(false),
            ])
            ->filtersFormColumns(2)
            ->filtersTriggerAction(
                fn(\Filament\Actions\Action $action) => $action
                    ->icon(Heroicon::OutlinedFunnel)
                    ->label('Filter')
            )

            // ═══════════════════════════════════════════════════════════════
            // ACTIONS
            // ═══════════════════════════════════════════════════════════════
            ->recordActions([
                ViewAction::make()
                    ->icon(Heroicon::OutlinedEye)
                    ->label('Lihat'),
                EditAction::make()
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->label('Edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->icon(Heroicon::OutlinedTrash)
                        ->label('Hapus Terpilih'),
                ]),
            ])

            // ═══════════════════════════════════════════════════════════════
            // TABLE OPTIONS
            // ═══════════════════════════════════════════════════════════════
            ->reorderable('order')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession()
            ->searchPlaceholder('Cari nama, jabatan, email...')
            ->deferLoading()
            ->poll(null);
    }
}

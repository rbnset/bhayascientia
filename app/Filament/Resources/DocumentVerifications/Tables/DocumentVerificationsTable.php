<?php
// app/Filament/Resources/DocumentVerifications/Tables/DocumentVerificationsTable.php

namespace App\Filament\Resources\DocumentVerifications\Tables;

use App\Filament\Resources\DocumentVerifications\DocumentVerificationResource;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteBulkAction as ActionsDeleteBulkAction;
use Filament\Actions\ViewAction as ActionsViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DocumentVerificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('last_scanned_at', 'desc')
            ->poll('30s')
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Verifikasi')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Kode disalin!')
                    ->fontFamily(FontFamily::Mono)
                    ->weight(FontWeight::Medium)
                    ->color('primary'),

                TextColumn::make('publicationVersion.publication.title')
                    ->label('Publikasi')
                    ->searchable()
                    ->sortable()
                    ->limit(38)
                    ->tooltip(fn($record) => $record->publicationVersion?->publication?->title)
                    ->placeholder('-'),

                TextColumn::make('publicationVersion.version_number')
                    ->label('Ver.')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn($state) => 'v' . $state)
                    ->alignCenter(),

                TextColumn::make('scan_count')
                    ->label('Scan')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state >= 50 => 'danger',
                        $state >= 10 => 'warning',
                        $state >= 1  => 'success',
                        default      => 'gray',
                    }),

                TextColumn::make('ip_address')
                    ->label('IP Terakhir')
                    ->fontFamily(FontFamily::Mono)
                    ->color('gray')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('last_scanned_at')
                    ->label('Terakhir Discan')
                    ->sortable()
                    ->since()
                    ->tooltip(fn($record) => $record->last_scanned_at?->format('d M Y, H:i:s'))
                    ->placeholder('Belum pernah')
                    ->color(fn($record) => $record->last_scanned_at?->isToday() ? 'warning' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Dicatat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('scanned_today')
                    ->label('Discan Hari Ini')
                    ->query(fn(Builder $q) => $q->whereDate('last_scanned_at', today()))
                    ->toggle(),

                Filter::make('high_scan')
                    ->label('Scan Tinggi (≥10)')
                    ->query(fn(Builder $q) => $q->where('scan_count', '>=', 10))
                    ->toggle(),

                Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn(Builder $q) => $q->whereBetween('last_scanned_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek(),
                    ]))
                    ->toggle(),

                Filter::make('never_scanned')
                    ->label('Belum Pernah Discan')
                    ->query(fn(Builder $q) => $q->whereNull('last_scanned_at'))
                    ->toggle(),
            ])
            ->actions([
                Action::make('open_verify')
                    ->label('Cek Publik')
                    ->icon(Heroicon::MagnifyingGlass)
                    ->color('gray')
                    ->url(fn($record) => route('document.verify', $record->code))
                    ->openUrlInNewTab(),

                ActionsViewAction::make()->label('Detail'),
            ])
            ->bulkActions([
                ActionsBulkActionGroup::make([
                    ActionsDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedShieldCheck)
            ->emptyStateHeading('Belum ada log verifikasi')
            ->emptyStateDescription('Log akan muncul otomatis saat seseorang melakukan scan verifikasi dokumen.');
    }
}

<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // ── Foto: pakai accessor photo_url dari User model ──────────
                ImageColumn::make('photo_url')
                    ->label('')
                    ->circular()
                    ->size(40)
                    // ✅ Accessor sudah handle: upload → Google → UI Avatars
                    ->defaultImageUrl(
                        fn($record) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($record->name) .
                            '&background=FF6B18&color=fff&size=80&bold=true'
                    )
                    ->toggleable(),

                // ── Nama + email sebagai deskripsi ──────────────────────────
                TextColumn::make('name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn($record) => $record->email),

                // ── Role badges ─────────────────────────────────────────────
                TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'super_admin' => 'danger',
                        'admin'       => 'warning',
                        'author'      => 'success',
                        'reviewer'    => 'info',
                        default       => 'gray',
                    })
                    ->separator(','),

                // ── Status author profile ───────────────────────────────────
                TextColumn::make('author_status')
                    ->label('Profil Author')
                    ->state(
                        fn($record) => $record->authorProfile()->exists()
                            ? 'Terhubung'
                            : ($record->hasRole('author') ? 'Belum Dibuat' : '—')
                    )
                    ->badge()
                    ->color(
                        fn($record) => $record->authorProfile()->exists()
                            ? 'success'
                            : ($record->hasRole('author') ? 'warning' : 'gray')
                    )
                    ->icon(
                        fn($record) => $record->authorProfile()->exists()
                            ? 'heroicon-o-link'
                            : ($record->hasRole('author') ? 'heroicon-o-exclamation-triangle' : null)
                    )
                    ->tooltip(
                        fn($record) => $record->authorProfile()->exists()
                            ? 'Terhubung ke profil author'
                            : ($record->hasRole('author')
                                ? 'Role author ada tapi profil author belum dibuat'
                                : 'User ini bukan author')
                    )
                    ->toggleable(),

                // ── Pekerjaan ───────────────────────────────────────────────
                TextColumn::make('job_title')
                    ->label('Pekerjaan')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                // ── WhatsApp ────────────────────────────────────────────────
                TextColumn::make('whatsapp_number')
                    ->label('WhatsApp')
                    ->copyable()
                    ->copyMessage('Nomor WhatsApp disalin')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                // ── Status verifikasi email ─────────────────────────────────
                TextColumn::make('email_verified_at')
                    ->label('Verifikasi')
                    ->badge()
                    ->sortable()
                    ->color(fn($state) => $state ? 'success' : 'gray')
                    ->formatStateUsing(fn($state) => $state ? 'Terverifikasi' : 'Belum'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                // Filter: berdasarkan role
                SelectFilter::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua Peran'),

                // Filter: status verifikasi email
                TernaryFilter::make('email_verified_at')
                    ->label('Status Verifikasi')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah Terverifikasi')
                    ->falseLabel('Belum Terverifikasi')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn(Builder $query) => $query->whereNull('email_verified_at'),
                        blank: fn(Builder $query) => $query,
                    ),

                // ✅ Filter: status author profile
                TernaryFilter::make('author_profile')
                    ->label('Profil Author')
                    ->placeholder('Semua User')
                    ->trueLabel('Sudah punya profil author')
                    ->falseLabel('Belum punya profil author')
                    ->queries(
                        true: fn(Builder $query) => $query->whereHas('author'),
                        false: fn(Builder $query) => $query->whereDoesntHave('author'),
                        blank: fn(Builder $query) => $query,
                    ),

                // Filter: tanggal dibuat
                Filter::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('created_until')->label('Sampai'),
                    ])
                    ->query(
                        fn(Builder $query, array $data) => $query
                            ->when(
                                $data['created_from'],
                                fn($q, $date) => $q->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn($q, $date) => $q->whereDate('created_at', '<=', $date)
                            )
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Dari: ' .
                                \Carbon\Carbon::parse($data['created_from'])->format('d M Y');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Sampai: ' .
                                \Carbon\Carbon::parse($data['created_until'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make()
                    ->label('Ubah')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->icon('heroicon-o-trash'),
                ]),
            ]);
    }
}

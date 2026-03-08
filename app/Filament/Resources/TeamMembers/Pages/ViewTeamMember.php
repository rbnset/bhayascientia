<?php

namespace App\Filament\Resources\TeamMembers\Pages;

use App\Filament\Resources\TeamMembers\TeamMemberResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ViewTeamMember extends ViewRecord
{
    protected static string $resource = TeamMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon(Heroicon::OutlinedPencilSquare)
                ->label('Edit Anggota'),

            DeleteAction::make()
                ->icon(Heroicon::OutlinedTrash)
                ->label('Hapus')
                ->requiresConfirmation()
                ->modalHeading('Hapus Anggota Tim')
                ->modalDescription('Tindakan ini tidak dapat dibatalkan. Data anggota ini akan dihapus permanen.')
                ->modalSubmitActionLabel('Ya, Hapus'),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ╔══════════════════════════════════════════════════════╗
                // ║  BLOK 1 — HERO: Identitas utama (paling penting)    ║
                // ║  Foto + Nama + Jabatan + Level + Status             ║
                // ║  → Siapa orang ini, one glance                      ║
                // ╚══════════════════════════════════════════════════════╝
                Section::make()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm'      => 4,
                        ])->schema([

                            // ── Foto ──────────────────────────────────
                            Section::make()
                                ->schema([
                                    ImageEntry::make('photo_url')
                                        ->label('')
                                        ->circular()
                                        ->size(120)
                                        ->defaultImageUrl(
                                            fn($record): string =>
                                            'https://ui-avatars.com/api/?name='
                                                . urlencode($record->name ?? 'NN')
                                                . '&size=200&background=FFF7F2&color=FF6B18&bold=true'
                                        )
                                        ->extraAttributes(['class' => 'flex justify-center']),
                                ])
                                ->columnSpan(1),

                            // ── Identitas Utama ───────────────────────
                            Section::make()
                                ->schema([
                                    Grid::make(2)->schema([

                                        TextEntry::make('name')
                                            ->label('Nama Lengkap')
                                            ->weight('bold')
                                            ->columnSpanFull(),

                                        TextEntry::make('title')
                                            ->label('Jabatan')
                                            ->icon(Heroicon::OutlinedBriefcase)
                                            ->color('gray'),

                                        TextEntry::make('department')
                                            ->label('Departemen')
                                            ->icon(Heroicon::OutlinedBuildingOffice2)
                                            ->placeholder('—')
                                            ->color('gray'),

                                        TextEntry::make('level')
                                            ->label('Level')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'leadership' => 'danger',
                                                'management' => 'warning',
                                                'department' => 'success',
                                                default      => 'gray',
                                            })
                                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                                'leadership' => 'Leadership',
                                                'management' => 'Management',
                                                'department' => 'Department',
                                                default      => ucfirst($state),
                                            }),

                                        IconEntry::make('is_active')
                                            ->label('Status')
                                            ->boolean()
                                            ->trueIcon(Heroicon::OutlinedCheckCircle)
                                            ->falseIcon(Heroicon::OutlinedXCircle)
                                            ->trueColor('success')
                                            ->falseColor('danger'),

                                    ]),
                                ])
                                ->columnSpan(3),

                        ]),
                    ]),

                // ╔══════════════════════════════════════════════════════╗
                // ║  BLOK 2 — KONTAK & BIO (sama pentingnya)            ║
                // ║  Email + LinkedIn | Bio                             ║
                // ║  → Cara menghubungi + konteks siapa dia            ║
                // ╚══════════════════════════════════════════════════════╝
                Grid::make([
                    'default' => 1,
                    'md'      => 2,
                ])->schema([

                    Section::make('Kontak')
                        ->icon(Heroicon::OutlinedEnvelope)
                        ->schema([

                            TextEntry::make('email')
                                ->label('Email')
                                ->icon(Heroicon::OutlinedEnvelope)
                                ->copyable()
                                ->copyMessage('Email disalin!')
                                ->copyMessageDuration(1500)
                                ->placeholder('—')
                                ->color('gray'),

                            TextEntry::make('linkedin')
                                ->label('LinkedIn')
                                ->icon(Heroicon::OutlinedLink)
                                ->url(fn($record) => $record->linkedin)
                                ->openUrlInNewTab()
                                ->placeholder('—')
                                ->color('info'),

                        ]),

                    Section::make('Bio')
                        ->icon(Heroicon::OutlinedDocumentText)
                        ->schema([

                            TextEntry::make('description')
                                ->label('')
                                ->placeholder('Belum ada deskripsi.')
                                ->prose()
                                ->markdown(),

                        ]),

                ]),

                // ╔══════════════════════════════════════════════════════╗
                // ║  BLOK 3 — PENGATURAN TAMPIL (konteks admin)         ║
                // ║  Urutan + Department card settings                  ║
                // ║  → Hanya relevan untuk admin                        ║
                // ╚══════════════════════════════════════════════════════╝
                Section::make('Pengaturan Tampil')
                    ->icon(Heroicon::OutlinedCog6Tooth)
                    ->description('Konfigurasi bagaimana anggota ini ditampilkan di halaman publik.')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'md'      => 3,
                        ])->schema([

                            TextEntry::make('order')
                                ->label('Urutan Tampil')
                                ->icon(Heroicon::OutlinedBarsArrowUp)
                                ->badge()
                                ->color('gray')
                                ->helperText('Semakin kecil, semakin awal tampil.'),

                            TextEntry::make('icon_type')
                                ->label('Ikon Departemen')
                                ->icon(Heroicon::OutlinedPhoto)
                                ->formatStateUsing(fn(?string $state): string => match ($state) {
                                    'code'       => 'Development',
                                    'content'    => 'Content',
                                    'marketing'  => 'Marketing',
                                    'operations' => 'Operations',
                                    'support'    => 'Support',
                                    default      => '—',
                                })
                                ->badge()
                                ->color('gray')
                                ->visible(fn($record): bool => $record?->level === 'department'),

                            TextEntry::make('member_count')
                                ->label('Jumlah Anggota')
                                ->icon(Heroicon::OutlinedUsers)
                                ->suffix(' orang')
                                ->badge()
                                ->color('info')
                                ->visible(fn($record): bool => $record?->level === 'department'),

                        ]),

                    ]),

                // ╔══════════════════════════════════════════════════════╗
                // ║  BLOK 4 — METADATA (paling tidak penting)           ║
                // ║  Timestamps → collapsed by default                  ║
                // ╚══════════════════════════════════════════════════════╝
                Section::make('Metadata')
                    ->icon(Heroicon::OutlinedInformationCircle)
                    ->description('Informasi teknis pencatatan data.')
                    ->collapsed()
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'md'      => 2,
                        ])->schema([

                            TextEntry::make('created_at')
                                ->label('Dibuat Pada')
                                ->icon(Heroicon::OutlinedCalendarDays)
                                ->dateTime('d F Y, H:i')
                                ->color('gray'),

                            TextEntry::make('updated_at')
                                ->label('Terakhir Diperbarui')
                                ->icon(Heroicon::OutlinedClock)
                                ->since()
                                ->tooltip(
                                    fn($record): string =>
                                    $record->updated_at?->translatedFormat('d F Y, H:i') ?? ''
                                )
                                ->color('gray'),

                        ]),

                    ]),

            ]);
    }
}

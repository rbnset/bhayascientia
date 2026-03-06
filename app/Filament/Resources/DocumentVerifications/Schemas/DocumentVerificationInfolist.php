<?php
// app/Filament/Resources/DocumentVerifications/Schemas/DocumentVerificationInfolist.php

namespace App\Filament\Resources\DocumentVerifications\Schemas;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;

class DocumentVerificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Informasi Verifikasi')
                ->icon(Heroicon::OutlinedQrCode)
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('code')
                            ->label('Kode Verifikasi')
                            ->copyable()
                            ->copyMessage('Kode berhasil disalin!')
                            ->fontFamily(FontFamily::Mono)
                            ->weight(FontWeight::Bold)
                            ->color('primary'),

                        TextEntry::make('scan_count')
                            ->label('Total Scan')
                            ->badge()
                            ->color('warning'),

                        TextEntry::make('last_scanned_at')
                            ->label('Terakhir Discan')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Belum pernah discan'),
                    ]),
                ]),

            Section::make('Dokumen Terkait')
                ->icon(Heroicon::OutlinedDocumentText)
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('publicationVersion.publication.title')
                            ->label('Judul Publikasi')
                            ->columnSpanFull()
                            ->weight(FontWeight::Medium),

                        TextEntry::make('publicationVersion.publication.author')
                            ->label('Penulis')
                            ->placeholder('-'),

                        TextEntry::make('publicationVersion.version_number')
                            ->label('Versi Dokumen')
                            ->formatStateUsing(fn($state) => 'Versi ' . $state),

                        TextEntry::make('publicationVersion.publication.status')
                            ->label('Status Publikasi')
                            ->badge()
                            ->formatStateUsing(fn($state) => match (strtolower($state ?? '')) {
                                'published' => 'Diterbitkan',
                                'draft'     => 'Draft',
                                'review'    => 'Dalam Review',
                                default     => ucfirst($state ?? '-'),
                            })
                            ->color(fn($state) => match (strtolower($state ?? '')) {
                                'published' => 'success',
                                'draft'     => 'warning',
                                'review'    => 'info',
                                default     => 'gray',
                            }),

                        TextEntry::make('publicationVersion.publication.id')
                            ->label('ID Publikasi')
                            ->formatStateUsing(fn($state) => '#' . str_pad($state, 4, '0', STR_PAD_LEFT))
                            ->fontFamily(FontFamily::Mono)
                            ->color('gray'),

                        TextEntry::make('publicationVersion.created_at')
                            ->label('Tanggal Versi Dibuat')
                            ->date('d M Y'),
                    ]),
                ]),

            Section::make('Data Teknis Scan Terakhir')
                ->icon(Heroicon::OutlinedServer)
                ->collapsed()
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('ip_address')
                            ->label('IP Address')
                            ->fontFamily(FontFamily::Mono)
                            ->copyable()
                            ->placeholder('-'),

                        TextEntry::make('created_at')
                            ->label('Pertama Kali Dicatat')
                            ->dateTime('d M Y, H:i'),

                        TextEntry::make('user_agent')
                            ->label('User Agent')
                            ->placeholder('-')
                            ->columnSpanFull()
                            ->limit(120)
                            ->tooltip(fn($record) => $record->user_agent),

                        TextEntry::make('metadata')
                            ->label('Metadata JSON')
                            ->formatStateUsing(fn($state) => $state
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                : '-')
                            ->fontFamily(FontFamily::Mono)
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),
                ]),

        ]);
    }
}

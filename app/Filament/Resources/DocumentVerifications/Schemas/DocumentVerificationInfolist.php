<?php
// app/Filament/Resources/DocumentVerifications/Schemas/DocumentVerificationInfolist.php

namespace App\Filament\Resources\DocumentVerifications\Schemas;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

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

                        // Authors via relasi — join nama
                        TextEntry::make('publicationVersion.publication.id')
                            ->label('Penulis')
                            ->columnSpanFull()
                            ->formatStateUsing(function ($state, $record) {
                                $authors = $record->publicationVersion
                                    ?->publication
                                    ?->authors;

                                if (! $authors || $authors->isEmpty()) {
                                    return '-';
                                }

                                return $authors->map(function ($author) {
                                    $label = $author->name;
                                    if ($author->pivot?->is_corresponding) {
                                        $label .= ' ✦';
                                    }
                                    return $label;
                                })->join(', ');
                            }),

                        TextEntry::make('publicationVersion.version_number')
                            ->label('Versi Dokumen')
                            ->formatStateUsing(fn($state) => 'Versi ' . $state),

                        TextEntry::make('publicationVersion.publication.status')
                            ->label('Status Publikasi')
                            ->badge()
                            ->formatStateUsing(fn($state) => match (strtolower($state ?? '')) {
                                'published'         => 'Diterbitkan',
                                'draft'             => 'Draft',
                                'in_review'         => 'Dalam Review',
                                'submitted'         => 'Terkirim',
                                'accepted'          => 'Diterima',
                                'rejected'          => 'Ditolak',
                                'revision_required' => 'Perlu Revisi',
                                default             => ucfirst($state ?? '-'),
                            })
                            ->color(fn($state) => match (strtolower($state ?? '')) {
                                'published', 'accepted' => 'success',
                                'draft', 'submitted'    => 'warning',
                                'in_review'             => 'info',
                                'rejected', 'revision_required' => 'danger',
                                default                 => 'gray',
                            }),

                        TextEntry::make('publicationVersion.publication.published_at')
                            ->label('Tanggal Diterbitkan')
                            ->date('d M Y')
                            ->placeholder('-'),

                        TextEntry::make('publicationVersion.publication.id')
                            ->label('ID Publikasi')
                            ->formatStateUsing(fn($state) => '#' . str_pad($state, 4, '0', STR_PAD_LEFT))
                            ->fontFamily(FontFamily::Mono)
                            ->color('gray'),
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

<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReviewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ── Informasi Publikasi ───────────────────────────────
            Section::make('Informasi Publikasi')
                ->icon('heroicon-o-document-text')
                ->columns(2)
                ->schema([
                    TextEntry::make('publicationVersion.publication.title')
                        ->label('Judul')
                        ->columnSpanFull()
                        ->weight('bold'),

                    TextEntry::make('publicationVersion.publication.publicationType.name')
                        ->label('Tipe'),

                    TextEntry::make('publicationVersion.version_number')
                        ->label('Versi Manuskrip')
                        ->formatStateUsing(fn($state) => 'v' . $state),

                    TextEntry::make('publicationVersion.publication.status')
                        ->label('Status Publikasi')
                        ->badge()
                        ->color(fn(string $state): string => match ($state) {
                            'draft'             => 'gray',
                            'submitted'         => 'blue',
                            'in_review'         => 'purple',
                            'revision_required' => 'warning',
                            'accepted'          => 'success',
                            'rejected'          => 'danger',
                            'published'         => 'success',
                            default             => 'gray',
                        })
                        ->formatStateUsing(fn(string $state): string => match ($state) {
                            'draft'             => 'Draft',
                            'submitted'         => 'Submitted',
                            'in_review'         => 'In Review',
                            'revision_required' => 'Revision Required',
                            'accepted'          => 'Accepted',
                            'rejected'          => 'Rejected',
                            'published'         => 'Published',
                            default             => $state,
                        }),

                    TextEntry::make('publicationVersion.submitted_at')
                        ->label('Tanggal Submit')
                        ->dateTime('d M Y, H:i'),
                ]),

            // ── Informasi Review ──────────────────────────────────
            Section::make('Informasi Review')
                ->icon('heroicon-o-user-circle')
                ->columns(2)
                ->schema([
                    TextEntry::make('reviewer.name')
                        ->label('Reviewer'),

                    TextEntry::make('decision')
                        ->label('Keputusan')
                        ->badge()
                        ->color(fn(?string $state): string => match ($state) {
                            'accepted'          => 'success',
                            'rejected'          => 'danger',
                            'revision_required' => 'warning',
                            default             => 'gray',
                        })
                        ->formatStateUsing(fn(?string $state): string => match ($state) {
                            'accepted'          => 'Diterima',
                            'rejected'          => 'Ditolak',
                            'revision_required' => 'Perlu Revisi',
                            default             => 'Belum diputuskan',
                        }),

                    TextEntry::make('created_at')
                        ->label('Tanggal Review Dibuat')
                        ->dateTime('d M Y, H:i'),

                    TextEntry::make('updated_at')
                        ->label('Terakhir Diperbarui')
                        ->dateTime('d M Y, H:i'),
                ]),

            // ── Catatan per Bagian ────────────────────────────────
            Section::make('Catatan per Bagian')
                ->icon('heroicon-o-clipboard-document-list')
                ->collapsed(false)
                ->schema([
                    RepeatableEntry::make('notes')
                        ->label('')
                        ->columnSpanFull()
                        ->columns(2)
                        ->schema([
                            TextEntry::make('section')
                                ->label('Bagian')
                                ->badge()
                                ->color('primary')
                                ->formatStateUsing(fn(string $state): string => match ($state) {
                                    'title'        => 'Title',
                                    'abstract'     => 'Abstract',
                                    'introduction' => 'Introduction',
                                    'methods'      => 'Methods',
                                    'results'      => 'Results',
                                    'discussion'   => 'Discussion',
                                    'conclusion'   => 'Conclusion',
                                    'references'   => 'References',
                                    default        => $state,
                                }),

                            TextEntry::make('note')
                                ->label('Catatan')
                                ->columnSpanFull()
                                ->html(),
                        ]),
                ]),

            // ── Komentar Umum ─────────────────────────────────────
            Section::make('Komentar Umum Reviewer')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->schema([
                    TextEntry::make('overall_comment')
                        ->label('')
                        ->html()
                        ->columnSpanFull(),
                ]),
        ]);
    }
}

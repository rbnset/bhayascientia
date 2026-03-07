<?php
// app/Filament/Resources/Authors/Schemas/AuthorInfolist.php

namespace App\Filament\Resources\Authors\Schemas;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class AuthorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Grid::make()
                ->columns(['default' => 1, 'lg' => 4])
                ->schema([

                    Section::make()
                        ->columnSpan(['default' => 1, 'lg' => 1])
                        ->schema([
                            ImageEntry::make('photo_url')
                                ->label('')
                                ->circular()
                                ->size(120)
                                ->defaultImageUrl(
                                    fn($record) =>
                                    'https://ui-avatars.com/api/?name=' .
                                        urlencode($record->getRawOriginal('name') ?? 'A') .
                                        '&background=FF6B18&color=fff&size=200&bold=true'
                                ),

                            TextEntry::make('claim_status')
                                ->label('Status')
                                ->state(fn($record) => $record->isClaimed()
                                    ? 'Linked ke Akun'
                                    : 'External Author')
                                ->badge()
                                ->color(fn($record) => $record->isClaimed()
                                    ? 'success'
                                    : 'info'),

                            TextEntry::make('pub_count')
                                ->label('Total Publikasi')
                                ->state(fn($record) => $record->publications()->count())
                                ->badge()
                                ->color('warning'),
                        ]),

                    Section::make('Identitas Author')
                        ->icon('heroicon-o-user-circle')
                        ->columnSpan(['default' => 1, 'lg' => 3])
                        ->schema([
                            Grid::make(2)->schema([
                                TextEntry::make('name')
                                    ->label('Nama Lengkap')
                                    ->weight(FontWeight::Bold)
                                    ->columnSpanFull()
                                    ->placeholder('—'),

                                TextEntry::make('email')
                                    ->label('Email')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable()
                                    ->copyMessage('Email disalin!')
                                    ->placeholder('—'),

                                TextEntry::make('affiliation')
                                    ->label('Affiliasi / Institusi')
                                    ->icon('heroicon-o-building-office')
                                    ->placeholder('—'),

                                TextEntry::make('user.name')
                                    ->label('Akun Terhubung')
                                    ->icon('heroicon-o-link')
                                    ->placeholder('Tidak terhubung ke akun manapun')
                                    ->description(fn($record) => $record->user?->email),

                                TextEntry::make('created_at')
                                    ->label('Terdaftar Sejak')
                                    ->icon('heroicon-o-calendar')
                                    ->date('d F Y'),
                            ]),
                        ]),
                ]),

            Section::make('Biografi')
                ->icon('heroicon-o-document-text')
                ->schema([
                    TextEntry::make('bio')
                        ->label('')
                        ->placeholder('Belum ada biografi.')
                        ->columnSpanFull(),
                ]),

            Section::make('Statistik Publikasi')
                ->icon('heroicon-o-chart-bar')
                ->columns(4)
                ->schema([
                    TextEntry::make('stat_total')
                        ->label('Total')
                        ->state(fn($record) => $record->publications()->count())
                        ->badge()
                        ->color('gray'),

                    TextEntry::make('stat_published')
                        ->label('Diterbitkan')
                        ->state(fn($record) => $record->publications()
                            ->where('status', 'published')->count())
                        ->badge()
                        ->color('success'),

                    TextEntry::make('stat_review')
                        ->label('Dalam Review')
                        ->state(fn($record) => $record->publications()
                            ->where('status', 'in_review')->count())
                        ->badge()
                        ->color('info'),

                    TextEntry::make('stat_revision')
                        ->label('Perlu Revisi')
                        ->state(fn($record) => $record->publications()
                            ->where('status', 'revision_required')->count())
                        ->badge()
                        ->color('danger'),
                ]),
        ]);
    }
}

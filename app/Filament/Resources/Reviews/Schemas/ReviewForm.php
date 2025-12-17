<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                // =========================
                // REVIEW CONTEXT
                // =========================
                Section::make('Review Context')
                    ->description('Informasi publikasi dan reviewer')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->schema([

                        Select::make('publication_version_id')
                            ->label('Publication Version')
                            ->relationship('publicationVersion', 'id')
                            ->getOptionLabelFromRecordUsing(
                                fn($record) => $record->display_label
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('reviewer_id')
                            ->label('Reviewer')
                            ->relationship('reviewer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                // =========================
                // REVIEW DECISION
                // =========================
                Section::make('Review Decision')
                    ->description('Keputusan hasil peninjauan')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([

                        Select::make('decision')
                            ->label('Decision')
                            ->required()
                            ->options([
                                'revision_required' => 'Revision Required',
                                'accepted' => 'Accepted',
                                'rejected' => 'Rejected',
                            ])
                            ->native(false)
                            ->helperText('Keputusan akhir reviewer terhadap versi ini'),
                    ]),

                // =========================
                // REVIEW COMMENT
                // =========================
                Section::make('Reviewer Comments')
                    ->description('Catatan dan masukan untuk penulis')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([

                        Textarea::make('overall_comment')
                            ->label('Overall Comment')
                            ->rows(6)
                            ->required()
                            ->placeholder('Tuliskan evaluasi, saran, dan catatan reviewer...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

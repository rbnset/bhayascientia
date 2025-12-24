<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Manuscript Annotator')
                    ->description('Preview PDF + highlight + notes (tersimpan sebagai JSON)')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        // ini yang nyimpen JSON annotation ke kolom reviews.annotations
                        ViewField::make('annotations')
                            ->view('filament.forms.components.pdf-annotator')
                            ->viewData([
                                'fileUrl' => fn($get) => $get('publication_version_id')
                                    ? route('manuscripts.view', $get('publication_version_id'))
                                    : null,
                            ])
                            ->columnSpanFull(),
                    ]),

                // =========================
                // REVIEW CONTEXT
                // =========================
                Section::make('Review Context')
                    ->description('Informasi publikasi dan reviewer')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->schema([
                        Select::make('publication_version_id')
                            ->label('Publication Version')
                            ->relationship(
                                name: 'publicationVersion',
                                modifyQueryUsing: fn($query) => $query->with('publication')->orderByDesc('created_at')
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->display_label)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),

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
                // REVIEW NOTES
                // =========================
                Section::make('Detailed Review Notes')
                    ->description('Catatan per bagian naskah')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        Repeater::make('notes')
                            ->relationship() // Review::notes()
                            ->schema([
                                Select::make('section')
                                    ->label('Section')
                                    ->options([
                                        'title' => 'Title',
                                        'abstract' => 'Abstract',
                                        'introduction' => 'Introduction',
                                        'methods' => 'Methods',
                                        'results' => 'Results',
                                        'discussion' => 'Discussion',
                                        'conclusion' => 'Conclusion',
                                        'references' => 'References',
                                    ])
                                    ->required(),

                                Textarea::make('note')
                                    ->label('Reviewer Note')
                                    ->rows(4)
                                    ->required()
                                    ->placeholder('Catatan spesifik untuk bagian ini...'),
                            ])
                            ->addActionLabel('Add Note')
                            ->reorderable()
                            ->collapsed()
                            ->columnSpanFull(),
                    ]),

                // =========================
                // REVIEW ATTACHMENTS
                // =========================
                Section::make('Annotated Manuscript')
                    ->description('Unggah PDF yang telah diberi catatan oleh reviewer (opsional)')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Repeater::make('attachments')
                            ->relationship() // Review::attachments()
                            ->schema([
                                FileUpload::make('file_path')
                                    ->label('Reviewed PDF')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->directory('review-attachments')
                                    ->preserveFilenames()
                                    ->downloadable()
                                    ->openable()
                                    ->required()
                                    ->maxSize(10240),
                            ])
                            ->addActionLabel('Add PDF')
                            ->collapsed()
                            ->columnSpanFull(),
                    ]),

                // =========================
                // REVIEW COMMENT
                // =========================
                Section::make('Reviewer Comments')
                    ->description('Catatan umum untuk penulis')
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

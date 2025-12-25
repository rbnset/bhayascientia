<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Get;

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
                            ->relationship(
                                name: 'publicationVersion',
                                modifyQueryUsing: fn($query) => $query
                                    ->with('publication')
                                    // hanya publication yang sedang proses review / minta revisi
                                    ->whereHas('publication', fn($q) => $q->whereIn('status', [
                                        'in_review',
                                        'revision_required',
                                    ]))
                                    // wajib sudah upload PDF
                                    ->whereNotNull('pdf_file_path')
                                    ->where('pdf_file_path', '!=', '')
                                    // hanya versi TERBARU per publication (asumsi id makin besar = versi makin baru)
                                    ->whereIn('id', function ($sub) {
                                        $sub->from('publication_versions as pv')
                                            ->selectRaw('MAX(pv.id)')
                                            ->groupBy('pv.publication_id');
                                    })
                                    // EXCLUDE: versi yang sudah pernah direview oleh user login
                                    ->whereNotExists(function ($sub) {
                                        $sub->selectRaw(1)
                                            ->from('reviews as r')
                                            ->whereColumn('r.publication_version_id', 'publication_versions.id')
                                            ->where('r.reviewer_id', auth()->id());
                                    })
                                    ->orderByDesc('created_at')
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->display_label)
                            ->searchable()
                            ->preload()
                            ->required(),


                        Select::make('reviewer_id')
                            ->label('Reviewer')
                            ->relationship(
                                name: 'reviewer',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) => $query->role('reviewer')
                            )
                            ->default(fn() => auth()->id())
                            ->disabled(fn() => auth()->user()?->hasRole('reviewer'))
                            ->dehydrated()
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
                                    ->maxSize(10240), // 10 MB
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

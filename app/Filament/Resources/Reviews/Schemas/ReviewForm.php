<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Wizard::make([
                    // =========================
                    // STEP 1: PICK & PREVIEW
                    // =========================
                    Step::make('Pilih naskah')
                        ->description('Pilih versi publikasi & lihat preview')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->schema([
                            Section::make('Review Context')
                                ->description('Pilih publication version & reviewer')
                                ->icon('heroicon-o-document-magnifying-glass')
                                ->schema([
                                    Select::make('publication_version_id')
                                        ->label('Publication Version')
                                        ->relationship(
                                            name: 'publicationVersion',
                                            // penting: operasi create vs edit dibedakan di sini
                                            modifyQueryUsing: function (Builder $query, string $operation) {
                                                // Selalu eager load supaya label/preview enak
                                                $query->with('publication');

                                                // Filter ketat hanya untuk CREATE
                                                if ($operation === 'create') {
                                                    $query
                                                        ->whereHas('publication', fn($q) => $q->whereIn('status', [
                                                            'in_review',
                                                            'revision_required',
                                                        ]))
                                                        ->whereNotNull('pdf_file_path')
                                                        ->where('pdf_file_path', '!=', '')
                                                        ->whereIn('id', function ($sub) {
                                                            $sub->from('publication_versions as pv')
                                                                ->selectRaw('MAX(pv.id)')
                                                                ->groupBy('pv.publication_id');
                                                        })
                                                        ->whereNotExists(function ($sub) {
                                                            $sub->selectRaw(1)
                                                                ->from('reviews as r')
                                                                ->whereColumn('r.publication_version_id', 'publication_versions.id')
                                                                ->where('r.reviewer_id', auth()->id());
                                                        })
                                                        ->orderByDesc('created_at');
                                                }

                                                // EDIT: jangan filter terlalu ketat,
                                                // supaya nilai yang sudah tersimpan tetap ada di options
                                                if ($operation === 'edit') {
                                                    $query->orderByDesc('created_at');
                                                }

                                                return $query;
                                            }
                                        )
                                        ->getOptionLabelFromRecordUsing(fn($record) => $record->display_label)
                                        ->searchable()
                                        ->preload()
                                        ->live()
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

                            Section::make('Preview publikasi')
                                ->description('Ringkasan metadata sebelum review')
                                ->icon('heroicon-o-eye')
                                ->schema([
                                    View::make('filament.reviews.publication-preview')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // =========================
                    // STEP 2: READ & WRITE REVIEW
                    // =========================
                    Step::make('Tulis review')
                        ->description('Baca PDF + isi catatan & komentar')
                        ->icon('heroicon-o-pencil-square')
                        ->schema([
                            Section::make('Manuscript PDF')
                                ->description('Baca naskah versi yang dipilih')
                                ->icon('heroicon-o-document-text')
                                ->schema([
                                    View::make('filament.reviews.pdf-viewer')
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Detailed Review Notes')
                                ->description('Catatan per bagian naskah')
                                ->icon('heroicon-o-clipboard-document-list')
                                ->schema([
                                    Repeater::make('notes')
                                        ->relationship()
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
                                                ->required()
                                                ->native(false),

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
                        ]),

                    // =========================
                    // STEP 3: ATTACHMENTS & DECISION
                    // =========================
                    Step::make('Finalisasi')
                        ->description('Upload anotasi & keputusan')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->schema([
                            Section::make('Annotated Manuscript')
                                ->description('Unggah PDF yang telah diberi catatan oleh reviewer (opsional)')
                                ->icon('heroicon-o-paper-clip')
                                ->schema([
                                    Repeater::make('attachments')
                                        ->relationship()
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
                        ]),
                ])
                    ->persistStepInQueryString(), // ini OK, bukan penyebab invalid [web:66]
            ]);
    }
}

<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Wizard::make([

                    /* ══════════════════════════════════════════════
                       STEP 1: Pilih naskah + Preview metadata
                    ══════════════════════════════════════════════ */
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
                                            modifyQueryUsing: function (Builder $query, string $operation) {
                                                $query->with('publication');

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

                            /*
                             * PERUBAHAN: Preview metadata publikasi tetap ada di Step 1
                             * agar reviewer tahu dulu apa yang akan di-review.
                             */
                            Section::make('Preview publikasi')
                                ->description('Ringkasan metadata sebelum review')
                                ->icon('heroicon-o-eye')
                                ->schema([
                                    View::make('filament.reviews.publication-preview')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    /* ══════════════════════════════════════════════
                       STEP 2: Baca PDF + Anotasi + Catatan
                    ══════════════════════════════════════════════ */
                    Step::make('Baca & Anotasi')
                        ->description('Baca PDF, beri anotasi, isi catatan reviewer')
                        ->icon('heroicon-o-pencil-square')
                        ->schema([

                            /*
                             * PDF VIEWER + ANNOTATION TOOL
                             * Reviewer membaca naskah dan memberi anotasi langsung.
                             * Anotasi tersimpan ke DB (pdf_annotations) via REST API.
                             * Tombol "Export PDF + Anotasi" akan mengunduh PDF yang sudah
                             * diberi markup — file ini bisa langsung di-upload di Step 3.
                             */
                            Section::make('Baca & Anotasi Naskah')
                                ->description('Beri highlight, komentar, dan tanda pada naskah. Anotasi tersimpan otomatis. Gunakan tombol "Export PDF + Anotasi" untuk mengunduh hasil markup.')
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
                                                    'title'        => 'Title',
                                                    'abstract'     => 'Abstract',
                                                    'introduction' => 'Introduction',
                                                    'methods'      => 'Methods',
                                                    'results'      => 'Results',
                                                    'discussion'   => 'Discussion',
                                                    'conclusion'   => 'Conclusion',
                                                    'references'   => 'References',
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
                                    \Filament\Forms\Components\RichEditor::make('overall_comment')
                                        ->label('Overall Comment')
                                        ->required()
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'underline',
                                            'strike',
                                            'link',
                                            'bulletList',
                                            'orderedList',
                                            'blockquote',
                                            'h2',
                                            'h3',
                                            'undo',
                                            'redo',
                                        ])
                                        ->placeholder('Tuliskan evaluasi, saran, dan catatan reviewer...')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    /* ══════════════════════════════════════════════
                       STEP 3: Finalisasi — Upload PDF & Keputusan
                    ══════════════════════════════════════════════ */
                    Step::make('Finalisasi')
                        ->description('Upload PDF beranotasi & keputusan akhir')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->schema([

                            Section::make('Annotated Manuscript')
                                ->description('Upload PDF yang sudah diberi anotasi (gunakan tombol "Export PDF + Anotasi" di Step 2 untuk mengunduhnya, lalu upload di sini).')
                                ->icon('heroicon-o-paper-clip')
                                ->schema([
                                    Repeater::make('attachments')
                                        ->relationship()
                                        ->maxItems(1)
                                        ->defaultItems(0)
                                        ->reorderable(false)
                                        ->addActionLabel('📎 Upload PDF Beranotasi (1x)')
                                        ->collapsed()
                                        ->columnSpanFull()
                                        ->schema([
                                            FileUpload::make('file_path')
                                                ->label('Reviewed PDF (dengan anotasi)')
                                                ->acceptedFileTypes(['application/pdf'])
                                                ->disk('local')
                                                ->visibility('private')
                                                ->directory('review-attachments')
                                                ->preserveFilenames()
                                                ->maxSize(20480) // 20MB — anotasi menambah ukuran
                                                ->openable(false)
                                                ->downloadable(false)
                                                ->required()
                                                ->helperText('Export PDF dari Step 2, lalu upload di sini.'),
                                        ]),

                                    Actions::make([
                                        Action::make('downloadAnnotatedPdf')
                                            ->label('Download PDF reviewer')
                                            ->icon('heroicon-o-arrow-down-tray')
                                            ->visible(fn($record) => filled($record?->attachments?->first()?->file_path))
                                            ->action(function ($record) {
                                                $attachment = $record->attachments()->latest()->first();
                                                abort_unless($attachment && filled($attachment->file_path), 404);
                                                return Storage::disk('local')->download($attachment->file_path);
                                            }),
                                    ])->columnSpanFull(),
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
                                            'accepted'          => 'Accepted',
                                            'rejected'          => 'Rejected',
                                        ])
                                        ->native(false)
                                        ->helperText('Keputusan akhir reviewer terhadap versi ini'),
                                ]),
                        ]),

                ])
                    ->persistStepInQueryString(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
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
                    Step::make('Pilih naskah')
                        ->description('Pilih versi publikasi & lihat preview')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->schema([
                            Section::make('Review Context')
                                ->description('Pilih publication version & reviewer')
                                ->icon('heroicon-o-document-magnifying-glass')
                                ->schema([

                                    // ✅ Info banner untuk opini tanpa manuskrip
                                    Placeholder::make('opini_info')
                                        ->label('')
                                        ->content(
                                            fn($record) => $record && is_null($record->publication_version_id)
                                                ? new \Illuminate\Support\HtmlString('
                                                <div style="
                                                    background:#FFF7ED;border:1.5px solid #FED7AA;
                                                    border-left:5px solid #F97316;border-radius:10px;
                                                    padding:14px 18px;font-size:13px;color:#92400E;line-height:1.6;
                                                ">
                                                    📝 <strong>Opini tanpa manuskrip</strong><br>
                                                    Publikasi ini adalah opini yang dikirim tanpa file PDF.
                                                    Review dilakukan berdasarkan isi opini yang tertulis di form publikasi.
                                                    Field "Publication Version" dikosongkan secara otomatis.
                                                </div>
                                            ')
                                                : null
                                        )
                                        ->visible(fn($record) => $record && is_null($record->publication_version_id))
                                        ->columnSpanFull(),

                                    // ✅ Select version — hidden & tidak required untuk opini
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
                                        ->nullable()
                                        // ✅ Tidak required jika ini opini (publication_version_id null di record)
                                        ->required(fn($record) => !($record && is_null($record->publication_version_id)))
                                        // ✅ Sembunyikan field jika opini (tidak ada version)
                                        ->visible(fn($record) => !($record && is_null($record->publication_version_id))),

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

                    Step::make('Tulis review')
                        ->description('Baca PDF + isi catatan & komentar')
                        ->icon('heroicon-o-pencil-square')
                        ->schema([
                            // ✅ PDF viewer hanya muncul jika ada manuskrip
                            Section::make('Manuscript PDF')
                                ->description('Baca naskah versi yang dipilih')
                                ->icon('heroicon-o-document-text')
                                ->visible(fn($record) => $record && filled($record->publicationVersion?->pdf_file_path))
                                ->schema([
                                    View::make('filament.reviews.pdf-viewer')
                                        ->columnSpanFull(),
                                ]),

                            // ✅ Untuk opini: tampilkan isi opini langsung
                            Section::make('Isi Opini')
                                ->description('Konten opini yang dikirim author')
                                ->icon('heroicon-o-document-text')
                                ->visible(fn($record) => $record && is_null($record->publication_version_id))
                                ->schema([
                                    Placeholder::make('opini_content')
                                        ->label('Isi Opini')
                                        ->content(fn($record) => new \Illuminate\Support\HtmlString(
                                            filled($record?->publication?->abstract)
                                                ? '<div class="fi-prose" style="max-height:600px;overflow-y:auto;padding:1rem;background:var(--fi-bg-muted,#f9fafb);border-radius:8px;border:1px solid #e5e7eb;">'
                                                . str($record->publication->abstract)->sanitizeHtml()
                                                . '</div>'
                                                : '<p style="color:#9ca3af;">Isi opini tidak tersedia.</p>'
                                        ))
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
                                                    'abstract'     => 'Abstract / Isi Opini',
                                                    'introduction' => 'Introduction',
                                                    'methods'      => 'Methods',
                                                    'results'      => 'Results',
                                                    'discussion'   => 'Discussion',
                                                    'conclusion'   => 'Conclusion',
                                                    'references'   => 'References',
                                                    'general'      => 'General (Opini)',
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

                    Step::make('Finalisasi')
                        ->description('Upload anotasi & keputusan')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->schema([
                            // ✅ Upload PDF anotasi hanya untuk non-opini
                            Section::make('Annotated Manuscript')
                                ->description('Unggah PDF yang telah diberi catatan (maksimal 1 file)')
                                ->icon('heroicon-o-paper-clip')
                                ->visible(fn($record) => !($record && is_null($record->publication_version_id)))
                                ->schema([
                                    Repeater::make('attachments')
                                        ->relationship()
                                        ->maxItems(1)
                                        ->defaultItems(0)
                                        ->reorderable(false)
                                        ->addActionLabel('Upload PDF (1x)')
                                        ->collapsed()
                                        ->columnSpanFull()
                                        ->schema([
                                            FileUpload::make('file_path')
                                                ->label('Reviewed PDF')
                                                ->acceptedFileTypes(['application/pdf'])
                                                ->disk('local')
                                                ->visibility('private')
                                                ->directory('review-attachments')
                                                ->preserveFilenames()
                                                ->maxSize(10240)
                                                ->openable(false)
                                                ->downloadable(false)
                                                ->required(),
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

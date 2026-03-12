<?php

namespace App\Filament\Resources\Publications\Schemas;

use App\Models\Author;
use App\Models\Category;
use App\Models\Keyword;
use App\Models\Method;
use App\Models\PublicationType;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class PublicationForm
{
    private static function isReviewer(): bool
    {
        return (bool) auth()->user()?->hasRole('reviewer');
    }

    private static function publicationTypeSlug(callable $get): ?string
    {
        $id = $get('publication_type_id');
        if (!$id) return null;

        return PublicationType::query()->whereKey($id)->value('slug');
    }

    private static function resolveCurrentAuthor(): ?Author
    {
        $currentUser = auth()->user();
        if (!$currentUser) return null;

        $author = Author::firstOrCreate(
            ['user_id' => $currentUser->id],
            [
                'name'        => null,
                'email'       => null,
                'affiliation' => null,
                'bio'         => null,
                'photo_path'  => null,
            ]
        );

        $author->setRelation('user', $currentUser);

        return $author;
    }

    private static function keywordCreateOptionForm(string $labelField = 'Keyword'): array
    {
        return [
            TextInput::make('name')
                ->label($labelField)
                ->required()
                ->maxLength(100)
                ->live(onBlur: true)
                ->unique(table: 'keywords', column: 'name', ignoreRecord: true)
                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),

            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->disabled()
                ->dehydrated()
                ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'super_admin'])),
        ];
    }

    private static function renderStatusBanner(?object $record): string
    {
        if (!$record) return '';

        $status = $record->status ?? 'draft';
        $isReviewer = self::isReviewer();
        $isAdmin    = auth()->user()?->hasAnyRole(['admin', 'super_admin']);
        $role       = match (true) {
            $isReviewer => 'reviewer',
            $isAdmin    => 'admin',
            default     => 'author',
        };

        // [status][role] => config
        $map = [
            'draft' => [
                'color'  => '#F59E0B',
                'bg' => '#FFFBEB',
                'border' => '#FDE68A',
                'icon'   => '✏️',
                'label' => 'Draft',
                'author'   => [
                    'title'   => 'Publikasi masih dalam tahap Draft',
                    'message' => 'Lengkapi semua informasi, lalu klik <strong>Submit Manuskrip</strong> di pojok kanan atas. Pastikan judul, abstrak, penulis, dan file sudah lengkap sebelum submit.',
                ],
                'reviewer' => [
                    'title'   => 'Naskah belum disubmit',
                    'message' => 'Author belum mengirimkan naskah ini. Tidak ada tindakan yang diperlukan saat ini.',
                ],
                'admin' => [
                    'title'   => 'Publikasi masih Draft',
                    'message' => 'Author belum melengkapi atau mengajukan naskah ini. Pantau progres atau hubungi author jika diperlukan.',
                ],
                'steps' => [
                    ['done' => true,  'text' => 'Buat publikasi'],
                    ['done' => false, 'text' => 'Submit ke reviewer'],
                    ['done' => false, 'text' => 'Proses review'],
                    ['done' => false, 'text' => 'Diterbitkan'],
                ],
            ],

            'submitted' => [
                'color'  => '#3B82F6',
                'bg' => '#EFF6FF',
                'border' => '#BFDBFE',
                'icon'   => '📬',
                'label' => 'Submitted',
                'author'   => [
                    'title'   => 'Naskah sudah dikirim ke reviewer',
                    'message' => 'Naskah kamu sudah diterima dan sedang <strong>menunggu reviewer ditugaskan</strong>. Kamu akan mendapat notifikasi saat proses review dimulai.',
                ],
                'reviewer' => [
                    'title'   => 'Naskah menunggu untuk direview',
                    'message' => 'Naskah ini sudah disubmit oleh author dan siap untuk direview. Klik tombol <strong>Review Naskah</strong> di pojok kanan atas untuk mulai.',
                ],
                'admin' => [
                    'title'   => 'Naskah menunggu reviewer',
                    'message' => 'Author telah mengirimkan naskah. Pastikan ada reviewer yang ditugaskan untuk memeriksa naskah ini.',
                ],
                'steps' => [
                    ['done' => true,  'text' => 'Buat publikasi'],
                    ['done' => true,  'text' => 'Submit ke reviewer'],
                    ['done' => false, 'text' => 'Proses review'],
                    ['done' => false, 'text' => 'Diterbitkan'],
                ],
            ],

            'in_review' => [
                'color'  => '#8B5CF6',
                'bg' => '#F5F3FF',
                'border' => '#DDD6FE',
                'icon'   => '🔍',
                'label' => 'In Review',
                'author'   => [
                    'title'   => 'Naskah sedang diperiksa reviewer',
                    'message' => 'Reviewer sedang <strong>memeriksa naskah kamu</strong>. Harap tunggu hasilnya. Jangan mengubah konten utama selama proses review berlangsung.',
                ],
                'reviewer' => [
                    'title'   => 'Anda sedang mereview naskah ini',
                    'message' => 'Buka halaman review melalui tombol <strong>Lihat Detail Review</strong> di atas untuk mengisi catatan dan menentukan keputusan.',
                ],
                'admin' => [
                    'title'   => 'Naskah sedang dalam proses review',
                    'message' => 'Reviewer sedang aktif memeriksa naskah. Pantau progress melalui halaman review.',
                ],
                'steps' => [
                    ['done' => true,  'text' => 'Buat publikasi'],
                    ['done' => true,  'text' => 'Submit ke reviewer'],
                    ['done' => true,  'text' => 'Proses review'],
                    ['done' => false, 'text' => 'Diterbitkan'],
                ],
            ],

            'revision_required' => [
                'color'  => '#EF4444',
                'bg' => '#FEF2F2',
                'border' => '#FECACA',
                'icon'   => '🔄',
                'label' => 'Revisi Diperlukan',
                'author'   => [
                    'title'   => 'Naskah kamu perlu direvisi',
                    'message' => 'Reviewer telah memberikan <strong>catatan revisi</strong>. Buka halaman review, pelajari catatan dari reviewer, lakukan perbaikan, lalu klik <strong>Upload Revisi</strong> di pojok kanan atas.',
                ],
                'reviewer' => [
                    'title'   => 'Anda telah meminta revisi — menunggu author',
                    'message' => 'Keputusan revisi sudah terkirim ke author. Anda akan mendapat notifikasi ketika author mengirimkan naskah yang telah diperbaiki. Gunakan tombol <strong>Review Revisi Terbaru</strong> saat revisi tiba.',
                ],
                'admin' => [
                    'title'   => 'Reviewer meminta revisi dari author',
                    'message' => 'Author telah dinotifikasi dan perlu mengirimkan ulang naskah yang diperbaiki. Pantau apakah author merespons dalam waktu yang wajar.',
                ],
                'steps' => [
                    ['done' => true,  'text' => 'Buat publikasi'],
                    ['done' => true,  'text' => 'Submit ke reviewer'],
                    ['done' => true,  'text' => 'Proses review'],
                    ['done' => false, 'text' => 'Revisi & resubmit'],
                ],
            ],

            'accepted' => [
                'color'  => '#10B981',
                'bg' => '#ECFDF5',
                'border' => '#A7F3D0',
                'icon'   => '✅',
                'label' => 'Accepted',
                'author'   => [
                    'title'   => 'Selamat! Naskah kamu diterima',
                    'message' => 'Naskah kamu telah <strong>diterima oleh reviewer</strong>. Tim editor akan segera menjadwalkan penerbitan. Tidak perlu melakukan perubahan apapun.',
                ],
                'reviewer' => [
                    'title'   => 'Anda telah menerima naskah ini',
                    'message' => 'Keputusan penerimaan sudah terkirim ke author. Naskah ini menunggu jadwal penerbitan dari editor/admin.',
                ],
                'admin' => [
                    'title'   => 'Naskah diterima — siap dijadwalkan terbit',
                    'message' => 'Reviewer telah menerima naskah ini. Silakan jadwalkan penerbitan dengan mengubah status ke <strong>Published</strong> pada Step Finalisasi.',
                ],
                'steps' => [
                    ['done' => true,  'text' => 'Buat publikasi'],
                    ['done' => true,  'text' => 'Submit ke reviewer'],
                    ['done' => true,  'text' => 'Proses review'],
                    ['done' => false, 'text' => 'Diterbitkan'],
                ],
            ],

            'rejected' => [
                'color'  => '#6B7280',
                'bg' => '#F9FAFB',
                'border' => '#E5E7EB',
                'icon'   => '❌',
                'label' => 'Rejected',
                'author'   => [
                    'title'   => 'Naskah tidak dapat diterima',
                    'message' => 'Mohon maaf, naskah kamu <strong>tidak dapat diterima</strong> saat ini. Baca catatan reviewer untuk mengetahui alasannya. Kamu dapat mengajukan naskah baru melalui menu <strong>Daftar Publikasi</strong>.',
                ],
                'reviewer' => [
                    'title'   => 'Anda telah menolak naskah ini',
                    'message' => 'Keputusan penolakan sudah terkirim ke author beserta catatan Anda. Tidak ada tindakan lebih lanjut yang diperlukan.',
                ],
                'admin' => [
                    'title'   => 'Naskah ditolak oleh reviewer',
                    'message' => 'Author telah dinotifikasi mengenai penolakan ini. Arsip tetap tersimpan untuk referensi.',
                ],
                'steps' => [
                    ['done' => true,  'text' => 'Buat publikasi'],
                    ['done' => true,  'text' => 'Submit ke reviewer'],
                    ['done' => true,  'text' => 'Proses review'],
                    ['done' => false, 'text' => 'Ditolak'],
                ],
            ],

            'published' => [
                'color'  => '#059669',
                'bg' => '#ECFDF5',
                'border' => '#6EE7B7',
                'icon'   => '🎉',
                'label' => 'Published',
                'author'   => [
                    'title'   => 'Naskah telah diterbitkan!',
                    'message' => 'Naskah kamu sudah <strong>live dan dapat diakses publik</strong>. Bagikan ke rekan-rekan dan komunitas untuk memperluas dampak karya ilmiahmu.',
                ],
                'reviewer' => [
                    'title'   => 'Naskah ini sudah diterbitkan',
                    'message' => 'Proses review selesai dan naskah sudah live. Terima kasih atas kontribusi review Anda.',
                ],
                'admin' => [
                    'title'   => 'Naskah sudah live dan dapat diakses publik',
                    'message' => 'Publikasi berhasil diterbitkan. Pastikan metadata dan URL sudah benar di halaman publik.',
                ],
                'steps' => [
                    ['done' => true, 'text' => 'Buat publikasi'],
                    ['done' => true, 'text' => 'Submit ke reviewer'],
                    ['done' => true, 'text' => 'Proses review'],
                    ['done' => true, 'text' => 'Diterbitkan'],
                ],
            ],
        ];

        $cfg     = $map[$status] ?? $map['draft'];
        $content = $cfg[$role] ?? $cfg['author'];

        // Build step indicators
        $stepsHtml = '';
        $stepCount = count($cfg['steps']);
        foreach ($cfg['steps'] as $i => $step) {
            $isLast   = $i === $stepCount - 1;
            $dotColor = $step['done'] ? $cfg['color'] : '#D1D5DB';
            $txtColor = $step['done'] ? $cfg['color'] : '#9CA3AF';
            $weight   = $step['done'] ? '600' : '400';

            $stepsHtml .= "
            <div style='display:flex;align-items:center;gap:6px;'>
                <div style='width:20px;height:20px;border-radius:50%;background:{$dotColor};
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;'>
                    <span style='color:white;font-size:11px;font-weight:700;'>"
                . ($step['done'] ? '✓' : ($i + 1))
                . "</span>
                </div>
                <span style='font-size:13px;color:{$txtColor};font-weight:{$weight};white-space:nowrap;'>{$step['text']}</span>
                " . (!$isLast ? "<div style='width:32px;height:2px;background:{$dotColor};margin:0 4px;border-radius:2px;'></div>" : '') . "
            </div>
        ";
        }

        $publishedAt = '';
        if ($status === 'published' && $record->published_at) {
            $date        = $record->published_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm');
            $publishedAt = "<div style='margin-top:8px;font-size:12px;color:{$cfg['color']};'>
                            🕐 Diterbitkan pada: <strong>{$date}</strong>
                        </div>";
        }

        return "
        <div style='
            background:{$cfg['bg']};
            border:1.5px solid {$cfg['border']};
            border-left:5px solid {$cfg['color']};
            border-radius:10px;
            padding:16px 20px;
            margin-bottom:4px;
        '>
            <div style='display:flex;align-items:flex-start;gap:12px;'>
                <span style='font-size:24px;line-height:1;flex-shrink:0;'>{$cfg['icon']}</span>
                <div style='flex:1;'>
                    <div style='display:flex;align-items:center;gap:8px;margin-bottom:6px;'>
                        <span style='
                            background:{$cfg['color']};color:white;font-size:11px;
                            font-weight:700;padding:2px 10px;border-radius:20px;
                            text-transform:uppercase;letter-spacing:0.5px;
                        '>{$cfg['label']}</span>
                    </div>
                    <div style='font-size:14px;font-weight:600;color:#1F2937;margin-bottom:4px;'>{$content['title']}</div>
                    <div style='font-size:13px;color:#4B5563;line-height:1.6;'>{$content['message']}</div>
                    {$publishedAt}
                </div>
            </div>
            <div style='
                display:flex;align-items:center;flex-wrap:wrap;gap:4px;
                margin-top:14px;padding-top:12px;
                border-top:1px solid {$cfg['border']};
            '>
                <span style='font-size:12px;color:#6B7280;margin-right:6px;'>Progress:</span>
                {$stepsHtml}
            </div>
        </div>
    ";
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // ─────────────────────────────────────────
                // STATUS BANNER — Ditampilkan di atas Wizard
                // ─────────────────────────────────────────
                Placeholder::make('status_banner')
                    ->label('Status Publikasi')
                    ->content(fn($record) => new \Illuminate\Support\HtmlString(
                        self::renderStatusBanner($record)
                    ))
                    ->visible(fn($record) => (bool) $record?->id)
                    ->columnSpanFull(),

                Wizard::make([

                    // ─────────────────────────────────────────
                    // STEP 1 — Informasi Publikasi
                    // ─────────────────────────────────────────
                    Step::make('Informasi Publikasi')
                        ->description('Tipe, judul, ringkasan, dan kata kunci')
                        ->icon('heroicon-o-document-text')
                        ->completedIcon('heroicon-o-check-circle')
                        ->columns(2)
                        ->schema([
                            Section::make('Publication Information')
                                ->description('Informasi utama karya ilmiah')
                                ->icon('heroicon-o-document-text')
                                ->columnSpanFull()
                                ->schema([

                                    Select::make('publication_type_id')
                                        ->label('Publication Type')
                                        ->relationship(
                                            name: 'publicationType',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn($query) => $query->where('is_active', true),
                                        )
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(fn($set) => $set('abstract', null))
                                        ->helperText('Pilih tipe publikasi terlebih dahulu agar field lain menyesuaikan.')
                                        ->disabled(fn() => self::isReviewer())
                                        ->columnSpan(1),

                                    // ✅ VALIDASI JUDUL UNIQUE
                                    TextInput::make('title')
                                        ->label(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Judul Artikel',
                                            'buku'   => 'Judul Buku',
                                            'opini'  => 'Judul Opini',
                                            default  => 'Judul Publikasi',
                                        })
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Contoh: Pengaruh X terhadap Y pada Konteks Z',
                                            'buku'   => 'Contoh: Panduan Lengkap Sistem Informasi',
                                            'opini'  => 'Contoh: Mengapa Digitalisasi Desa Masih Lambat?',
                                            default  => 'Tulis judul yang jelas dan ringkas.',
                                        })
                                        // ✅ Validasi unique: judul tidak boleh sama dengan publikasi lain
                                        ->unique(
                                            table: 'publications',
                                            column: 'title',
                                            ignoreRecord: true
                                        )
                                        ->validationMessages([
                                            'unique' => 'Judul karya ilmiah ini sudah pernah digunakan. Silakan gunakan judul yang berbeda atau tambahkan penjelasan spesifik (metode, lokasi, atau konteks).',
                                        ])
                                        ->disabled(fn() => self::isReviewer())
                                        ->columnSpanFull(),

                                    // Jurnal → Abstrak
                                    RichEditor::make('abstract')
                                        ->columnSpanFull()
                                        ->label('Abstrak')
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->required(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->toolbarButtons([
                                            ['bold', 'italic', 'underline', 'strike', 'link'],
                                            ['bulletList', 'orderedList', 'blockquote'],
                                            ['undo', 'redo'],
                                        ])
                                        ->helperText('Wajib. Tulis abstrak sesuai standar jurnal.')
                                        ->disabled(fn() => self::isReviewer()),

                                    // Buku → Sinopsis
                                    RichEditor::make('abstract')
                                        ->columnSpanFull()
                                        ->label('Sinopsis')
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'buku')
                                        ->required(false)
                                        ->toolbarButtons([
                                            ['bold', 'italic', 'underline', 'link'],
                                            ['bulletList', 'orderedList'],
                                            ['undo', 'redo'],
                                        ])
                                        ->helperText('Opsional. Tulis sinopsis menarik.')
                                        ->disabled(fn() => self::isReviewer()),

                                    // Opini → Isi Opini
                                    RichEditor::make('abstract')
                                        ->columnSpanFull()
                                        ->label('Isi Opini')
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'opini')
                                        ->required(fn($get) => self::publicationTypeSlug($get) === 'opini')
                                        ->toolbarButtons([
                                            ['bold', 'italic', 'underline', 'strike', 'link'],
                                            ['bulletList', 'orderedList', 'blockquote', 'h2', 'h3'],
                                            ['undo', 'redo'],
                                        ])
                                        ->helperText('Wajib. Tulis isi opini secara lengkap.')
                                        ->disabled(fn() => self::isReviewer()),

                                    // Keywords — Jurnal (min 3, maks 7)
                                    Select::make('keywords')
                                        ->label('Keywords')
                                        ->searchPrompt('Ketik kata kunci...')
                                        ->noSearchResultsMessage('Kata kunci tidak ditemukan. Klik ＋ untuk menambahkan baru.')
                                        ->relationship('keywords', 'name', fn($query) => $query->orderBy('name'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->minItems(3)
                                        ->maxItems(7)
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->required(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->createOptionForm(self::keywordCreateOptionForm('Keyword'))
                                        ->createOptionUsing(fn(array $data) => Keyword::create($data)->getKey())
                                        ->helperText('Wajib. Pilih minimal 3 dan maksimal 7 keyword.')
                                        ->disabled(fn() => self::isReviewer())
                                        ->columnSpanFull(),

                                    // Tags — Buku (maks 3)
                                    Select::make('keywords')
                                        ->label('Tags')
                                        ->searchPrompt('Ketik tag...')
                                        ->noSearchResultsMessage('Tag tidak ditemukan. Klik ＋ untuk menambahkan baru.')
                                        ->relationship('keywords', 'name', fn($query) => $query->orderBy('name'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->maxItems(3)
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'buku')
                                        ->required(false)
                                        ->createOptionForm(self::keywordCreateOptionForm('Tag'))
                                        ->createOptionUsing(fn(array $data) => Keyword::create($data)->getKey())
                                        ->helperText('Opsional. Maksimal 3 tag.')
                                        ->disabled(fn() => self::isReviewer())
                                        ->columnSpanFull(),

                                    // Topik — Opini (maks 3)
                                    Select::make('keywords')
                                        ->label('Topik')
                                        ->searchPrompt('Ketik topik...')
                                        ->noSearchResultsMessage('Topik tidak ditemukan. Klik ＋ untuk menambahkan baru.')
                                        ->relationship('keywords', 'name', fn($query) => $query->orderBy('name'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->maxItems(3)
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'opini')
                                        ->required(false)
                                        ->createOptionForm(self::keywordCreateOptionForm('Topik'))
                                        ->createOptionUsing(fn(array $data) => Keyword::create($data)->getKey())
                                        ->helperText('Opsional. Maksimal 3 topik.')
                                        ->disabled(fn() => self::isReviewer())
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // ─────────────────────────────────────────
                    // STEP 2 — Klasifikasi
                    // ─────────────────────────────────────────
                    Step::make('Klasifikasi')
                        ->description('Kategori & metode penelitian')
                        ->icon('heroicon-o-tag')
                        ->completedIcon('heroicon-o-check-circle')
                        ->columns(2)
                        ->schema([
                            Section::make('Classification')
                                ->description('Kategori dan metode penelitian')
                                ->icon('heroicon-o-tag')
                                ->columnSpanFull()
                                ->schema([

                                    Select::make('categories')
                                        ->label('Category')
                                        ->searchPrompt('Ketik nama kategori...')
                                        ->noSearchResultsMessage('Kategori tidak ditemukan. Klik ＋ untuk menambahkan baru.')
                                        ->relationship('categories', 'name', fn($query) => $query->orderBy('name'))
                                        ->multiple()->maxItems(1)->searchable()->preload()->required()
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('Nama Kategori')
                                                ->required()
                                                ->maxLength(100)
                                                ->live(onBlur: true)
                                                ->unique(table: 'categories', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),

                                            TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->disabled()
                                                ->dehydrated()
                                                ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'super_admin'])),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Category::create($data)->getKey())
                                        ->helperText('Pilih 1 kategori.')
                                        ->disabled(fn() => self::isReviewer())->columnSpan(1),

                                    Select::make('method_id')
                                        ->label(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Research Method',
                                            'buku'   => 'Metode Penulisan',
                                            default  => 'Research Method',
                                        })
                                        ->searchPrompt('Ketik nama metode penelitian...')
                                        ->noSearchResultsMessage('Metode tidak ditemukan. Klik ＋ untuk menambahkan baru.')
                                        ->relationship('method', 'name', fn($query) => $query->orderBy('name'))
                                        ->searchable()->preload()
                                        ->visible(fn($get) => self::publicationTypeSlug($get) !== 'opini')
                                        ->required(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('Nama Metode')
                                                ->required()
                                                ->maxLength(100)
                                                ->live(onBlur: true)
                                                ->unique(table: 'methods', column: 'name', ignoreRecord: true)
                                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),

                                            TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->disabled()
                                                ->dehydrated()
                                                ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'super_admin'])),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Method::create($data)->getKey())
                                        ->helperText(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Wajib. Pilih metode penelitian.',
                                            'buku'   => 'Opsional.',
                                            default  => '',
                                        })
                                        ->disabled(fn() => self::isReviewer())->columnSpan(1),

                                    Placeholder::make('method_info')
                                        ->label('')
                                        ->content('Opini tidak memerlukan klasifikasi metode penelitian.')
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'opini')
                                        ->columnSpan(1),
                                ]),
                        ]),

                    // ─────────────────────────────────────────
                    // STEP 3 — Penulis
                    // ─────────────────────────────────────────
                    Step::make('Penulis')
                        ->description('Penulis utama & tambahan')
                        ->icon('heroicon-o-users')
                        ->completedIcon('heroicon-o-check-circle')
                        ->schema([
                            Section::make('Authors')
                                ->description('Penulis corresponding diisi otomatis dari akun yang login.')
                                ->icon('heroicon-o-users')
                                ->schema([
                                    Repeater::make('authorPublications')
                                        ->label('Authors')
                                        ->deletable(!self::isReviewer())
                                        ->deleteAction(
                                            fn(\Filament\Actions\Action $action) => $action
                                                ->requiresConfirmation()
                                                ->modalHeading('Hapus Author?')
                                                ->modalDescription('Author ini akan dihapus dari publikasi. Tindakan ini tidak dapat dibatalkan.')
                                                ->modalSubmitActionLabel('Ya, Hapus')
                                                ->color('danger')
                                                ->hidden(function (array $arguments, \Filament\Forms\Components\Repeater $component): bool {
                                                    // Reviewer tidak boleh hapus
                                                    if (self::isReviewer()) return true;

                                                    $items    = $component->getState();
                                                    $authorId = $items[$arguments['item']]['author_id'] ?? null;

                                                    if (!$authorId) return false;

                                                    // Sembunyikan jika ini author milik user yang sedang login
                                                    $myAuthorId = \App\Models\Author::where('user_id', auth()->id())->value('id');

                                                    return (int) $authorId === (int) $myAuthorId;
                                                })
                                        )
                                        ->relationship('authorPublications')
                                        ->orderColumn('order')
                                        ->reorderable()
                                        ->defaultItems(0)
                                        ->minItems(1)
                                        ->addActionLabel('Tambah penulis lain')
                                        ->collapsed(false)  // ← semua item terbuka saat pertama load
                                        ->collapseAllAction(
                                            fn(\Filament\Actions\Action $action) => $action->label('Ciutkan semua')
                                        )
                                        ->expandAllAction(
                                            fn(\Filament\Actions\Action $action) => $action->label('Buka semua')
                                        )
                                        ->itemLabel(function (array $state): ?string {
                                            $authorId = $state['author_id'] ?? null;
                                            if (!$authorId) return 'Penulis baru';

                                            $author = \App\Models\Author::find($authorId);
                                            if (!$author) return 'Penulis';

                                            $label = $author->name;
                                            if ($state['is_corresponding'] ?? false) {
                                                $label .= ' · Corresponding';
                                            }

                                            return $label;
                                        })
                                        ->afterStateHydrated(function (?array $state, callable $set) {
                                            $state ??= [];
                                            $state = array_values($state);

                                            if (count($state) > 0) {
                                                foreach ($state as $i => $row) {
                                                    $state[$i]['order'] = $i + 1;
                                                }
                                                $set('authorPublications', $state);
                                                return;
                                            }

                                            $author = self::resolveCurrentAuthor();
                                            if (!$author) return;

                                            $set('authorPublications', [[
                                                'author_id'        => $author->id,
                                                'is_corresponding' => true,
                                                'order'            => 1,
                                            ]]);
                                        })
                                        ->schema([
                                            Select::make('author_id')
                                                ->label('Author')
                                                ->searchPrompt('Ketik nama atau email penulis...')
                                                ->noSearchResultsMessage('Penulis tidak ditemukan. Klik ＋ untuk menambahkan sebagai penulis baru.')
                                                ->required()
                                                ->live()
                                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                                ->disabled(fn(Get $get): bool => (bool) $get('is_corresponding'))
                                                ->relationship('author', 'name')
                                                ->searchable()
                                                ->getSearchResultsUsing(function (string $search) {
                                                    return Author::query()
                                                        ->with('user')
                                                        ->where(function ($q) use ($search) {
                                                            $q->where('authors.name', 'like', "%{$search}%")
                                                                ->orWhere('authors.email', 'like', "%{$search}%")
                                                                ->orWhereHas(
                                                                    'user',
                                                                    fn($u) =>
                                                                    $u->where('name', 'like', "%{$search}%")
                                                                        ->orWhere('email', 'like', "%{$search}%")
                                                                );
                                                        })
                                                        ->limit(20)
                                                        ->get()
                                                        ->mapWithKeys(function (Author $author) {
                                                            $label = $author->name;
                                                            if ($author->email) $label .= " — {$author->email}";
                                                            if ($author->affiliation) $label .= " ({$author->affiliation})";
                                                            return [$author->id => $label];
                                                        });
                                                })
                                                ->getOptionLabelUsing(function ($value): string {
                                                    $author = Author::with('user')->find($value);
                                                    if (!$author) return '—';
                                                    $label = $author->name;
                                                    if ($author->email) $label .= " — {$author->email}";
                                                    return $label;
                                                })
                                                ->dehydrated()
                                                ->createOptionForm([

                                                    // ── Foto Profil ───────────────────────────────────────────
                                                    FileUpload::make('photo_path')
                                                        ->label('Foto Profil')
                                                        ->avatar()
                                                        ->disk('public')
                                                        ->directory('authors/photos')
                                                        ->visibility('public')
                                                        ->imageEditor()
                                                        ->circleCropper()
                                                        ->imageEditorMode(2)
                                                        ->maxSize(2048)
                                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                                        ->helperText('JPG, PNG. Maks 2MB. Opsional.')
                                                        ->moveFiles()
                                                        ->extraAttributes([
                                                            'class' => 'flex flex-col items-center justify-center',
                                                        ]),

                                                    // ── Nama & Email ──────────────────────────────────────────
                                                    Grid::make()
                                                        ->columns(['default' => 1, 'md' => 2])
                                                        ->schema([
                                                            TextInput::make('name')
                                                                ->label('Nama Lengkap')
                                                                ->required()
                                                                ->maxLength(255)
                                                                ->placeholder('Contoh: Dr. John Doe, M.T.')
                                                                ->prefixIcon('heroicon-o-user')
                                                                ->helperText('Wajib untuk external author (tanpa akun).'),

                                                            TextInput::make('email')
                                                                ->label('Email')
                                                                ->email()
                                                                ->maxLength(255)
                                                                ->placeholder('john@example.com')
                                                                ->prefixIcon('heroicon-o-envelope')
                                                                ->unique(table: 'authors', column: 'email', ignoreRecord: true)
                                                                ->helperText('Opsional.'),
                                                        ]),

                                                    // ── Affiliasi ─────────────────────────────────────────────
                                                    TextInput::make('affiliation')
                                                        ->label('Affiliasi / Institusi')
                                                        ->maxLength(255)
                                                        ->placeholder('Universitas / Organisasi')
                                                        ->prefixIcon('heroicon-o-building-office')
                                                        ->helperText('Opsional.'),

                                                    // ── Bio ───────────────────────────────────────────────────
                                                    Textarea::make('bio')
                                                        ->label('Biografi')
                                                        ->rows(4)
                                                        ->maxLength(1000)
                                                        ->placeholder('Tulis bio singkat penulis...')
                                                        ->helperText('Opsional. Maks. 1000 karakter.'),

                                                    // ── Hubungkan ke Akun User ────────────────────────────────
                                                    Select::make('user_id')
                                                        ->label('Hubungkan ke Akun Pengguna')
                                                        ->relationship(
                                                            name: 'user',
                                                            titleAttribute: 'name',
                                                            modifyQueryUsing: fn($query) => $query
                                                                ->whereDoesntHave('author')
                                                                ->orderBy('name')
                                                        )
                                                        ->getOptionLabelFromRecordUsing(
                                                            fn(\App\Models\User $user) => "{$user->name} — {$user->email}"
                                                        )
                                                        ->searchable(['name', 'email'])
                                                        ->preload()
                                                        ->nullable()
                                                        ->placeholder('— Tidak terhubung (External Author) —')
                                                        ->prefixIcon('heroicon-o-link')
                                                        ->helperText('Opsional. Hubungkan ke akun user yang sudah terdaftar.')
                                                        ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'super_admin']))
                                                        ->afterStateUpdated(function ($state, callable $set) {
                                                            if ($state) {
                                                                $set('name', null);
                                                                $set('email', null);
                                                            }
                                                        }),
                                                ])
                                                ->createOptionUsing(fn(array $data) => Author::create($data)->getKey()),

                                            Checkbox::make('is_corresponding')
                                                ->label('Corresponding author')
                                                ->disabled()
                                                ->dehydrated(),
                                        ])
                                        ->mutateDehydratedStateUsing(function (array $state): array {
                                            $state = array_values(array_filter(
                                                $state,
                                                fn($row) => !empty($row['author_id'])
                                            ));

                                            $hasCorresponding = collect($state)->contains(
                                                fn($row) => (bool) ($row['is_corresponding'] ?? false)
                                            );

                                            if (!$hasCorresponding && count($state) > 0) {
                                                $state[0]['is_corresponding'] = true;
                                            }

                                            $already = false;
                                            foreach ($state as $i => $row) {
                                                $state[$i]['order'] = $i + 1;
                                                $isCorr = (bool) ($row['is_corresponding'] ?? false);
                                                if ($isCorr && !$already) {
                                                    $already = true;
                                                    $state[$i]['is_corresponding'] = true;
                                                } else {
                                                    $state[$i]['is_corresponding'] = false;
                                                }
                                            }

                                            return $state;
                                        })
                                        ->columnSpanFull()
                                        ->disabled(fn() => self::isReviewer()),
                                ]),
                        ]),

                    // ─────────────────────────────────────────
                    // STEP 4 — Finalisasi
                    // ─────────────────────────────────────────
                    Step::make('Finalisasi')
                        ->description('Cover, status, dan tanggal publikasi')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->completedIcon('heroicon-o-check-circle')
                        ->columns(2)
                        ->schema([
                            Section::make('Cover & Files')
                                ->description('Media pendukung publikasi')
                                ->icon('heroicon-o-photo')
                                ->columnSpan(1)
                                ->schema([
                                    FileUpload::make('cover_image_path')
                                        ->label('Cover Image')
                                        ->image()
                                        ->disk('public')
                                        ->directory('publications/covers')
                                        ->visibility('public')
                                        ->imageEditor()
                                        ->imageEditorAspectRatios([null, '2:3'])
                                        ->imageCropAspectRatio('2:3')
                                        ->imageResizeTargetWidth(600)
                                        ->imageResizeTargetHeight(900)
                                        ->imageResizeMode('cover')
                                        ->imagePreviewHeight('300')
                                        ->maxSize(2048)
                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                        ->helperText('Format: JPG/PNG/WebP. Maks. 2MB. Rasio ideal 2:3.')
                                        ->disabled(fn() => self::isReviewer())
                                        ->live(),
                                ]),

                            Section::make('Publication Status')
                                ->description('Status proses publikasi')
                                ->icon('heroicon-o-clipboard-document-check')
                                ->columnSpan(1)
                                ->hidden(fn() => auth()->user()?->hasRole('author'))
                                ->schema([
                                    Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'draft'             => 'Draft',
                                            'submitted'         => 'Submitted',
                                            'in_review'         => 'In Review',
                                            'revision_required' => 'Revision Required',
                                            'accepted'          => 'Accepted',
                                            'rejected'          => 'Rejected',
                                            'published'         => 'Published',
                                        ])
                                        ->default('draft')
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                            if ($state === 'published' && blank($get('published_at'))) {
                                                $set('published_at', now());
                                            }
                                            if ($state !== 'published') {
                                                $set('published_at', null);
                                            }
                                        })
                                        ->disabled(fn() => auth()->user()?->hasRole('author'))
                                        ->dehydrated(),

                                    DateTimePicker::make('published_at')
                                        ->label('Published At')
                                        ->visible(fn(Get $get) => $get('status') === 'published')
                                        ->disabled(fn() => auth()->user()?->hasRole('author'))
                                        ->dehydrated(fn(Get $get) => $get('status') === 'published')
                                        ->helperText('Diisi otomatis saat status berubah ke Published.'),
                                ]),
                        ]),

                    // ─────────────────────────────────────────
                    // STEP 5 — Preview
                    // ─────────────────────────────────────────
                    Step::make('Preview')
                        ->description('Cek tampilan sebelum simpan')
                        ->icon('heroicon-o-eye')
                        ->completedIcon('heroicon-o-check-circle')
                        ->schema([
                            Placeholder::make('preview_cover')
                                ->label('Cover Image')
                                ->content(function (Get $get, $record): \Illuminate\Support\HtmlString {
                                    $coverState = $get('cover_image_path');

                                    $url = null;

                                    // Kasus 1: FileUpload state berisi TemporaryUploadedFile (baru diupload)
                                    if (is_array($coverState)) {
                                        $first = array_values(array_filter($coverState))[0] ?? null;

                                        if ($first instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                            // Gunakan Storage::disk('local') karena Livewire menyimpan di local sementara
                                            $url = $first->temporaryUrl();
                                        } elseif (is_string($first) && filled($first)) {
                                            // String path — cek di public storage
                                            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($first)) {
                                                $url = \Illuminate\Support\Facades\Storage::disk('public')->url($first);
                                            }
                                        }
                                    }

                                    // Kasus 2: Sudah tersimpan di record (edit mode)
                                    if (!$url && $record?->cover_image_path) {
                                        $path = $record->cover_image_path;
                                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                                            $url = \Illuminate\Support\Facades\Storage::disk('public')->url($path);
                                        }
                                    }

                                    if (!$url) {
                                        return new \Illuminate\Support\HtmlString("
                        <div style='
                            width:200px;height:280px;
                            background:#F3F4F6;border:2px dashed #D1D5DB;
                            border-radius:10px;display:flex;align-items:center;
                            justify-content:center;color:#9CA3AF;font-size:13px;
                            text-align:center;padding:16px;
                        '>Belum ada cover image</div>
                    ");
                                    }

                                    $safeUrl = e($url);
                                    return new \Illuminate\Support\HtmlString("
                    <img
                        src='{$safeUrl}'
                        style='width:200px;height:280px;object-fit:cover;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.15);'
                        alt='Cover preview'
                    />
                ");
                                })
                                ->columnSpanFull(),

                            View::make('filament.publications.preview')
                                ->viewData(['titleLabel' => 'Judul']),
                        ]),

                ])
                    ->skippable()
                    ->persistStepInQueryString()
                    ->nextAction(
                        fn(Action $action) => $action
                            ->label('Lanjut')
                            ->icon('heroicon-o-arrow-right')
                            ->iconPosition('after')
                    )
                    ->previousAction(
                        fn(Action $action) => $action
                            ->label('Kembali')
                            ->icon('heroicon-o-arrow-left')
                            ->color('gray')
                    ),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Publications\Schemas;

use App\Models\Author;
use App\Models\Category;
use App\Models\Keyword;
use App\Models\Method;
use App\Models\PublicationType;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
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
    // ══════════════════════════════════════════════════════════════════════════
    // HELPER METHODS — ROLE CHECK
    // ══════════════════════════════════════════════════════════════════════════

    private static function isReviewer(): bool
    {
        return (bool) auth()->user()?->hasRole('reviewer');
    }

    private static function isAuthor(): bool
    {
        return (bool) auth()->user()?->hasRole('author');
    }

    private static function isAdmin(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(['admin', 'super_admin']);
    }

    private static function isContentDisabled(?object $record): bool
    {
        if (self::isReviewer()) return true;

        if (self::isAuthor()) {
            if (!$record?->id) return false;
            return !in_array($record->status ?? 'draft', ['draft', 'revision_required'], true);
        }

        return false;
    }

    private static function isFieldDisabled(): bool
    {
        return self::isReviewer();
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

    // ══════════════════════════════════════════════════════════════════════════
    // STATUS BANNER
    // ══════════════════════════════════════════════════════════════════════════

    private static function renderStatusBanner(?object $record): string
    {
        if (!$record) return '';

        $status     = $record->status ?? 'draft';
        $isReviewer = self::isReviewer();
        $isAdmin    = auth()->user()?->hasAnyRole(['admin', 'super_admin']);
        $role       = match (true) {
            $isReviewer => 'reviewer',
            $isAdmin    => 'admin',
            default     => 'author',
        };

        $map = [
            'draft' => [
                'color' => '#F59E0B',
                'bg' => '#FFFBEB',
                'border' => '#FDE68A',
                'icon' => '✏️',
                'label' => 'Draft',
                'author'   => ['title' => 'Publikasi masih dalam tahap Draft', 'message' => 'Lengkapi semua informasi, lalu klik <strong>Submit Manuskrip</strong> di pojok kanan atas.'],
                'reviewer' => ['title' => 'Naskah belum disubmit', 'message' => 'Author belum mengirimkan naskah ini.'],
                'admin'    => ['title' => 'Publikasi masih Draft', 'message' => 'Author belum melengkapi atau mengajukan naskah ini.'],
                'steps' => [['done' => true, 'text' => 'Buat publikasi'], ['done' => false, 'text' => 'Submit ke reviewer'], ['done' => false, 'text' => 'Proses review'], ['done' => false, 'text' => 'Diterbitkan']],
            ],
            'submitted' => [
                'color' => '#3B82F6',
                'bg' => '#EFF6FF',
                'border' => '#BFDBFE',
                'icon' => '📬',
                'label' => 'Submitted',
                'author'   => ['title' => 'Naskah sudah dikirim ke reviewer', 'message' => 'Naskah kamu sedang <strong>menunggu reviewer ditugaskan</strong>.'],
                'reviewer' => ['title' => 'Naskah menunggu untuk direview', 'message' => 'Klik tombol <strong>Review Naskah</strong> di pojok kanan atas untuk mulai.'],
                'admin'    => ['title' => 'Naskah menunggu reviewer', 'message' => 'Pastikan ada reviewer yang ditugaskan.'],
                'steps' => [['done' => true, 'text' => 'Buat publikasi'], ['done' => true, 'text' => 'Submit ke reviewer'], ['done' => false, 'text' => 'Proses review'], ['done' => false, 'text' => 'Diterbitkan']],
            ],
            'in_review' => [
                'color' => '#8B5CF6',
                'bg' => '#F5F3FF',
                'border' => '#DDD6FE',
                'icon' => '🔍',
                'label' => 'In Review',
                'author'   => ['title' => 'Naskah sedang diperiksa reviewer', 'message' => 'Harap tunggu hasilnya.'],
                'reviewer' => ['title' => 'Anda sedang mereview naskah ini', 'message' => 'Buka halaman review untuk mengisi catatan dan keputusan.'],
                'admin'    => ['title' => 'Naskah sedang dalam proses review', 'message' => 'Reviewer sedang aktif memeriksa naskah.'],
                'steps' => [['done' => true, 'text' => 'Buat publikasi'], ['done' => true, 'text' => 'Submit ke reviewer'], ['done' => true, 'text' => 'Proses review'], ['done' => false, 'text' => 'Diterbitkan']],
            ],
            'revision_required' => [
                'color' => '#EF4444',
                'bg' => '#FEF2F2',
                'border' => '#FECACA',
                'icon' => '🔄',
                'label' => 'Revisi Diperlukan',
                'author'   => ['title' => 'Naskah kamu perlu direvisi', 'message' => 'Buka catatan reviewer, lakukan perbaikan, lalu klik <strong>Upload Revisi</strong>.'],
                'reviewer' => ['title' => 'Anda telah meminta revisi — menunggu author', 'message' => 'Anda akan mendapat notifikasi ketika author mengirimkan naskah yang telah diperbaiki.'],
                'admin'    => ['title' => 'Reviewer meminta revisi dari author', 'message' => 'Author telah dinotifikasi.'],
                'steps' => [['done' => true, 'text' => 'Buat publikasi'], ['done' => true, 'text' => 'Submit ke reviewer'], ['done' => true, 'text' => 'Proses review'], ['done' => false, 'text' => 'Revisi & resubmit']],
            ],
            'accepted' => [
                'color' => '#10B981',
                'bg' => '#ECFDF5',
                'border' => '#A7F3D0',
                'icon' => '✅',
                'label' => 'Accepted',
                'author'   => ['title' => 'Selamat! Naskah kamu diterima', 'message' => 'Tim editor akan segera menjadwalkan penerbitan.'],
                'reviewer' => ['title' => 'Anda telah menerima naskah ini', 'message' => 'Keputusan penerimaan sudah terkirim ke author.'],
                'admin'    => ['title' => 'Naskah diterima — siap dijadwalkan terbit', 'message' => 'Silakan jadwalkan penerbitan.'],
                'steps' => [['done' => true, 'text' => 'Buat publikasi'], ['done' => true, 'text' => 'Submit ke reviewer'], ['done' => true, 'text' => 'Proses review'], ['done' => false, 'text' => 'Diterbitkan']],
            ],
            'rejected' => [
                'color' => '#6B7280',
                'bg' => '#F9FAFB',
                'border' => '#E5E7EB',
                'icon' => '❌',
                'label' => 'Rejected',
                'author'   => ['title' => 'Naskah tidak dapat diterima', 'message' => 'Baca catatan reviewer untuk mengetahui alasannya.'],
                'reviewer' => ['title' => 'Anda telah menolak naskah ini', 'message' => 'Keputusan penolakan sudah terkirim ke author.'],
                'admin'    => ['title' => 'Naskah ditolak oleh reviewer', 'message' => 'Author telah dinotifikasi.'],
                'steps' => [['done' => true, 'text' => 'Buat publikasi'], ['done' => true, 'text' => 'Submit ke reviewer'], ['done' => true, 'text' => 'Proses review'], ['done' => false, 'text' => 'Ditolak']],
            ],
            'published' => [
                'color' => '#059669',
                'bg' => '#ECFDF5',
                'border' => '#6EE7B7',
                'icon' => '🎉',
                'label' => 'Published',
                'author'   => ['title' => 'Naskah telah diterbitkan!', 'message' => 'Naskah kamu sudah <strong>live dan dapat diakses publik</strong>.'],
                'reviewer' => ['title' => 'Naskah ini sudah diterbitkan', 'message' => 'Proses review selesai dan naskah sudah live.'],
                'admin'    => ['title' => 'Naskah sudah live dan dapat diakses publik', 'message' => 'Publikasi berhasil diterbitkan.'],
                'steps' => [['done' => true, 'text' => 'Buat publikasi'], ['done' => true, 'text' => 'Submit ke reviewer'], ['done' => true, 'text' => 'Proses review'], ['done' => true, 'text' => 'Diterbitkan']],
            ],
        ];

        $cfg     = $map[$status] ?? $map['draft'];
        $content = $cfg[$role] ?? $cfg['author'];

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
                . ($step['done'] ? '✓' : ($i + 1)) . "</span>
                </div>
                <span style='font-size:13px;color:{$txtColor};font-weight:{$weight};white-space:nowrap;'>{$step['text']}</span>
                " . (!$isLast ? "<div style='width:32px;height:2px;background:{$dotColor};margin:0 4px;border-radius:2px;'></div>" : '') . "
            </div>";
        }

        $publishedAt = '';
        if ($status === 'published' && $record->published_at) {
            $date        = $record->published_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm');
            $publishedAt = "<div style='margin-top:8px;font-size:12px;color:{$cfg['color']};'>🕐 Diterbitkan pada: <strong>{$date}</strong></div>";
        }

        return "
        <div style='background:{$cfg['bg']};border:1.5px solid {$cfg['border']};border-left:5px solid {$cfg['color']};border-radius:10px;padding:16px 20px;margin-bottom:4px;'>
            <div style='display:flex;align-items:flex-start;gap:12px;'>
                <span style='font-size:24px;line-height:1;flex-shrink:0;'>{$cfg['icon']}</span>
                <div style='flex:1;'>
                    <div style='display:flex;align-items:center;gap:8px;margin-bottom:6px;'>
                        <span style='background:{$cfg['color']};color:white;font-size:11px;font-weight:700;padding:2px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:0.5px;'>{$cfg['label']}</span>
                    </div>
                    <div style='font-size:14px;font-weight:600;color:#1F2937;margin-bottom:4px;'>{$content['title']}</div>
                    <div style='font-size:13px;color:#4B5563;line-height:1.6;'>{$content['message']}</div>
                    {$publishedAt}
                </div>
            </div>
            <div style='display:flex;align-items:center;flex-wrap:wrap;gap:4px;margin-top:14px;padding-top:12px;border-top:1px solid {$cfg['border']};'>
                <span style='font-size:12px;color:#6B7280;margin-right:6px;'>Progress:</span>
                {$stepsHtml}
            </div>
        </div>";
    }

    // ══════════════════════════════════════════════════════════════════════════
    // LOA HTML (Letter of Agreement) — disusun sesuai UU Hak Cipta No. 28/2014
    // dan standar internasional (Berne Convention, WIPO Copyright Treaty)
    // ══════════════════════════════════════════════════════════════════════════

    private static function renderLoaContent(): string
    {
        $platformName = config('app.name', 'Dabraka');
        $today        = now()->locale('id')->isoFormat('D MMMM YYYY');

        return "
        <div style='
            font-family: Georgia, serif;
            background: #FAFAFA;
            border: 1px solid #E5E7EB;
            border-left: 4px solid #1D4ED8;
            border-radius: 8px;
            padding: 28px 32px;
            font-size: 13.5px;
            line-height: 1.9;
            color: #1F2937;
            max-height: 480px;
            overflow-y: auto;
        '>
            <div style='text-align:center;margin-bottom:20px;'>
                <div style='font-size:18px;font-weight:700;color:#1D4ED8;letter-spacing:0.5px;'>
                    SURAT PERNYATAAN DAN PERSETUJUAN HAK CIPTA
                </div>
                <div style='font-size:12px;color:#6B7280;margin-top:4px;'>
                    Letter of Agreement (LOA) — Platform {$platformName}
                </div>
                <div style='font-size:11px;color:#9CA3AF;'>Dokumen ini berlaku sejak tanggal pengajuan</div>
            </div>

            <p>Dengan mengirimkan karya ilmiah, buku, atau opini (<strong>\"Karya\"</strong>) melalui platform <strong>{$platformName}</strong> (<strong>\"Platform\"</strong>), Penulis (<strong>\"Anda\"</strong>) menyatakan, menjamin, dan menyetujui hal-hal berikut:</p>

            <div style='background:#EFF6FF;border:1px solid #BFDBFE;border-radius:6px;padding:14px 18px;margin:16px 0;'>
                <div style='font-weight:700;color:#1D4ED8;margin-bottom:8px;font-size:14px;'>§ 1. KEASLIAN & KEPEMILIKAN HAK CIPTA</div>
                <p style='margin:0;'>Anda menjamin bahwa:</p>
                <ul style='margin:8px 0 0 0;padding-left:20px;'>
                    <li>Karya adalah hasil karya intelektual Anda sendiri dan/atau para penulis yang tercantum.</li>
                    <li>Anda memiliki hak dan wewenang penuh untuk menyerahkan Karya kepada Platform.</li>
                    <li>Karya tidak melanggar hak cipta, hak milik intelektual, privasi, atau hak-hak sah pihak lain mana pun.</li>
                    <li>Jika Karya merupakan hasil karya bersama, Anda telah mendapatkan persetujuan dari seluruh rekan penulis untuk menyerahkan Karya kepada Platform.</li>
                    <li>Karya tidak mengandung konten yang bersifat fitnah, memfitnah, tidak senonoh, atau melanggar hukum yang berlaku di Indonesia maupun hukum internasional yang relevan.</li>
                </ul>
            </div>

            <div style='background:#F0FDF4;border:1px solid #BBF7D0;border-radius:6px;padding:14px 18px;margin:16px 0;'>
                <div style='font-weight:700;color:#166534;margin-bottom:8px;font-size:14px;'>§ 2. LISENSI KEPADA PLATFORM & OPEN ACCESS</div>
                <p style='margin:0;'>Dengan menyerahkan Karya, Anda memberikan kepada Platform:</p>
                <ul style='margin:8px 0 0 0;padding-left:20px;'>
                    <li>Hak non-eksklusif, bebas royalti, dan berlaku di seluruh dunia untuk menyimpan, menampilkan, memperbanyak, mendistribusikan, dan mengarsipkan Karya untuk tujuan akademis dan pendidikan.</li>
                    <li>Hak untuk mengindeks, mempromosikan, dan memperlihatkan metadata Karya kepada mesin pencari dan agregator akademik.</li>
                    <li><strong>Karya akan dipublikasikan secara Open Access</strong> dan dapat diakses secara gratis oleh publik. Platform tidak memungut biaya publikasi (<em>Article Processing Charge</em>) maupun biaya akses (<em>paywall</em>) kepada pembaca.</li>
                    <li>Anda <strong>tetap mempertahankan hak cipta</strong> atas Karya Anda. Platform hanya mendapatkan lisensi tampil sebagaimana diuraikan di atas.</li>
                </ul>
            </div>

            <div style='background:#FFF7ED;border:1px solid #FED7AA;border-radius:6px;padding:14px 18px;margin:16px 0;'>
                <div style='font-weight:700;color:#C2410C;margin-bottom:8px;font-size:14px;'>§ 3. BATASAN TANGGUNG JAWAB PLATFORM</div>
                <p style='margin:0;'><strong>Platform {$platformName} berfungsi semata-mata sebagai repositori digital dan sarana diseminasi ilmu pengetahuan. Dengan demikian:</strong></p>
                <ul style='margin:8px 0 0 0;padding-left:20px;'>
                    <li>Platform <strong>tidak bertanggung jawab</strong> atas kebenaran, akurasi, validitas ilmiah, atau orisinalitas Karya yang Anda serahkan.</li>
                    <li>Platform <strong>tidak bertanggung jawab</strong> atas segala klaim, tuntutan, gugatan, kerugian, atau kerusakan yang timbul akibat pelanggaran hak cipta, plagiarisme, atau pelanggaran hak pihak ketiga lainnya yang dilakukan oleh Penulis.</li>
                    <li>Platform <strong>tidak bertanggung jawab</strong> atas penggunaan Karya oleh pihak ketiga yang mengakses Karya melalui Platform.</li>
                    <li><strong>Penulis sepenuhnya dan secara pribadi bertanggung jawab</strong> atas segala konsekuensi hukum, sosial, dan etika yang timbul dari konten Karya.</li>
                    <li>Ketentuan ini berlaku sesuai dengan <strong>Undang-Undang No. 28 Tahun 2014 tentang Hak Cipta</strong>, <strong>UU No. 11 Tahun 2008 jo. UU No. 19 Tahun 2016 tentang ITE</strong>, <strong>Konvensi Berne</strong>, dan <strong>WIPO Copyright Treaty (WCT)</strong>.</li>
                </ul>
            </div>

            <div style='background:#FEF2F2;border:1px solid #FECACA;border-radius:6px;padding:14px 18px;margin:16px 0;'>
                <div style='font-weight:700;color:#B91C1C;margin-bottom:8px;font-size:14px;'>§ 4. KEBIJAKAN TAKEDOWN (PENCABUTAN KARYA)</div>
                <p style='margin:0;'>Anda memahami dan menyetujui bahwa:</p>
                <ul style='margin:8px 0 0 0;padding-left:20px;'>
                    <li>Platform berhak untuk <strong>menghapus, menarik, atau menonaktifkan akses</strong> terhadap Karya <strong>sewaktu-waktu tanpa pemberitahuan terlebih dahulu</strong> apabila terdapat laporan atau dugaan pelanggaran hak cipta, etika akademis, atau ketentuan hukum yang berlaku.</li>
                    <li>Laporan pelanggaran dapat diajukan oleh siapa pun kepada tim admin Platform melalui saluran pengaduan resmi.</li>
                    <li>Penghapusan Karya oleh Platform <strong>tidak membebaskan</strong> Penulis dari tanggung jawab hukum atas pelanggaran yang telah terjadi.</li>
                    <li>Penulis <strong>dapat dimintai pertanggungjawaban</strong> sesuai dengan hukum yang berlaku, termasuk namun tidak terbatas pada tuntutan perdata maupun pidana berdasarkan UU Hak Cipta dan/atau UU ITE.</li>
                    <li>Platform akan berupaya memberikan notifikasi kepada Penulis setelah tindakan takedown, namun tidak diwajibkan untuk melakukannya dalam keadaan mendesak atau jika terdapat perintah hukum.</li>
                </ul>
            </div>

            <div style='background:#F5F3FF;border:1px solid #DDD6FE;border-radius:6px;padding:14px 18px;margin:16px 0;'>
                <div style='font-weight:700;color:#5B21B6;margin-bottom:8px;font-size:14px;'>§ 5. KARYA YANG SUDAH PERNAH DITERBITKAN (PRIOR PUBLICATION)</div>
                <p style='margin:0;'>Jika Karya yang Anda ajukan telah dipublikasikan sebelumnya di platform atau media lain:</p>
                <ul style='margin:8px 0 0 0;padding-left:20px;'>
                    <li>Anda wajib mengungkapkan informasi publikasi sebelumnya secara jujur dan lengkap pada formulir yang disediakan.</li>
                    <li>Anda menjamin bahwa karya tersebut berstatus <strong>Open Access</strong> di platform asalnya, atau Anda telah memperoleh izin tertulis dari penerbit asli untuk mendistribusikan ulang karya tersebut.</li>
                    <li>Anda bertanggung jawab penuh atas kebenaran status keterbukaan (Open Access) karya yang Anda nyatakan.</li>
                    <li>Platform berhak untuk memverifikasi status OA karya dan menonaktifkan publikasi jika ditemukan ketidaksesuaian.</li>
                </ul>
            </div>

            <div style='background:#F9FAFB;border:1px solid #E5E7EB;border-radius:6px;padding:14px 18px;margin:16px 0;'>
                <div style='font-weight:700;color:#374151;margin-bottom:8px;font-size:14px;'>§ 6. HUKUM YANG BERLAKU & PENYELESAIAN SENGKETA</div>
                <ul style='margin:0;padding-left:20px;'>
                    <li>Persetujuan ini diatur oleh hukum <strong>Negara Kesatuan Republik Indonesia</strong>.</li>
                    <li>Segala sengketa yang timbul dari Persetujuan ini akan diselesaikan melalui musyawarah mufakat, dan jika tidak tercapai kesepakatan, akan diselesaikan melalui Pengadilan Negeri yang berwenang di Indonesia.</li>
                    <li>Penulis mengakui bahwa tanda persetujuan elektronik ini memiliki kekuatan hukum yang setara dengan tanda tangan basah sesuai dengan UU No. 11 Tahun 2008 tentang Informasi dan Transaksi Elektronik.</li>
                </ul>
            </div>

            <p style='margin-top:20px;font-size:12px;color:#6B7280;border-top:1px solid #E5E7EB;padding-top:12px;'>
                <em>Dokumen LOA ini dihasilkan secara otomatis oleh sistem Platform {$platformName}. Persetujuan Anda dicatat beserta timestamp, alamat IP, dan identitas akun sebagai bukti elektronik yang sah. Versi terbaru LOA berlaku pada setiap pengajuan baru.</em>
            </p>
        </div>";
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STEP 0 — Deklarasi & Persetujuan LOA
    // ══════════════════════════════════════════════════════════════════════════

    private static function buildLoaStep(): Step
    {
        return Step::make('Deklarasi & Persetujuan')
            ->description('Baca dan setujui pernyataan hak cipta sebelum melanjutkan')
            ->icon('heroicon-o-shield-check')
            ->completedIcon('heroicon-o-check-circle')
            ->schema([
                // ─── Tampilkan LOA HTML ───────────────────────────────────
                Placeholder::make('loa_content')
                    ->label('')
                    ->content(fn() => new \Illuminate\Support\HtmlString(self::renderLoaContent()))
                    ->columnSpanFull(),

                // ─── Checklist Persetujuan ────────────────────────────────
                Section::make('Pernyataan Penulis')
                    ->description('Centang semua pernyataan di bawah ini untuk melanjutkan.')
                    ->icon('heroicon-o-pencil-square')
                    ->columnSpanFull()
                    ->schema([

                        Checkbox::make('loa_is_original_work')
                            ->label('Saya menyatakan bahwa karya ini adalah karya orisinal saya dan/atau rekan penulis yang tercantum, bukan hasil plagiarisme atau pelanggaran hak cipta pihak lain.')
                            ->required()
                            ->accepted()
                            ->validationMessages(['accepted' => 'Anda harus mencentang pernyataan ini.'])
                            ->disabled(fn() => self::isFieldDisabled())
                            ->columnSpanFull(),

                        Checkbox::make('loa_grants_display_rights')
                            ->label('Saya memberikan izin kepada Platform untuk menampilkan, mendistribusikan, dan mengarsipkan karya ini secara Open Access dan bebas biaya.')
                            ->required()
                            ->accepted()
                            ->validationMessages(['accepted' => 'Anda harus mencentang pernyataan ini.'])
                            ->disabled(fn() => self::isFieldDisabled())
                            ->columnSpanFull(),

                        Checkbox::make('loa_platform_not_liable')
                            ->label('Saya memahami dan menyetujui bahwa Platform tidak bertanggung jawab atas klaim hak cipta, plagiarisme, atau pelanggaran hukum lainnya yang mungkin timbul dari karya ini. Saya menanggung seluruh tanggung jawab tersebut secara pribadi.')
                            ->required()
                            ->accepted()
                            ->validationMessages(['accepted' => 'Anda harus mencentang pernyataan ini.'])
                            ->disabled(fn() => self::isFieldDisabled())
                            ->columnSpanFull(),

                        Checkbox::make('loa_agrees_takedown_policy')
                            ->label('Saya menyetujui bahwa Platform berhak menghapus karya ini sewaktu-waktu tanpa pemberitahuan apabila terdapat laporan pelanggaran yang sah, dan hal tersebut tidak membebaskan saya dari tanggung jawab hukum.')
                            ->required()
                            ->accepted()
                            ->validationMessages(['accepted' => 'Anda harus mencentang pernyataan ini.'])
                            ->disabled(fn() => self::isFieldDisabled())
                            ->columnSpanFull(),

                        Checkbox::make('loa_agreed')
                            ->label('✅ Saya telah membaca, memahami sepenuhnya, dan menyetujui seluruh isi Surat Pernyataan dan Persetujuan Hak Cipta (LOA) di atas.')
                            ->required()
                            ->accepted()
                            ->validationMessages(['accepted' => 'Anda harus menyetujui LOA untuk melanjutkan.'])
                            ->live()
                            ->afterStateUpdated(function (bool $state, Set $set) {
                                if ($state) {
                                    $set('loa_agreed_at', now()->toIso8601String());
                                    $set('loa_agreed_ip', request()->ip());
                                    $set('loa_agreed_user_agent', substr(request()->userAgent() ?? '', 0, 500));
                                } else {
                                    $set('loa_agreed_at', null);
                                    $set('loa_agreed_ip', null);
                                    $set('loa_agreed_user_agent', null);
                                }
                            })
                            ->disabled(fn() => self::isFieldDisabled())
                            ->columnSpanFull(),

                        // Hidden fields untuk menyimpan metadata persetujuan
                        TextInput::make('loa_agreed_at')->hidden()->dehydrated(),
                        TextInput::make('loa_agreed_ip')->hidden()->dehydrated(),
                        TextInput::make('loa_agreed_user_agent')->hidden()->dehydrated(),
                    ]),

                // ─── Info box untuk reviewer/admin ────────────────────────
                Placeholder::make('loa_info_admin')
                    ->label('')
                    ->content(new \Illuminate\Support\HtmlString("
                        <div style='background:#EFF6FF;border:1px solid #BFDBFE;border-radius:6px;padding:12px 16px;font-size:13px;color:#1E40AF;'>
                            ℹ️ <strong>Catatan:</strong> Metadata persetujuan LOA (waktu, IP address, user agent) dicatat otomatis oleh sistem sebagai bukti elektronik yang sah sesuai UU ITE No. 11/2008.
                        </div>
                    "))
                    ->visible(fn() => self::isAdmin())
                    ->columnSpanFull(),
            ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STEP 1 — Prior Publication (Status Publikasi Sebelumnya)
    // ══════════════════════════════════════════════════════════════════════════


    private static function buildPriorPublicationStep(): Step
    {
        return Step::make('Status Publikasi')
            ->description('Apakah karya ini pernah diterbitkan sebelumnya?')
            ->icon('heroicon-o-globe-alt')
            ->completedIcon('heroicon-o-check-circle')
            ->columns(2)
            ->schema([
                Section::make('Riwayat Publikasi')
                    ->description('Deklarasikan apakah karya ini merupakan karya baru atau sudah pernah dipublikasikan di platform/media lain.')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->columnSpanFull()
                    ->schema([

                        // ─── STEP 1: Pilih Tipe Publikasi DULU ───────────────
                        // Ini akan auto-fill field publication_type_id di Step 2
                        Select::make('publication_type_id')
                            ->label('Tipe Karya yang Akan Didaftarkan')
                            ->relationship(
                                name: 'publicationType',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) => $query->where('is_active', true),
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->placeholder('— Pilih tipe karya terlebih dahulu —')
                            ->helperText('Wajib dipilih dahulu. Jenis karya menentukan identifier yang dibutuhkan (DOI / ISBN / Nama Media).')
                            ->prefixIcon('heroicon-o-document-text')
                            ->disabled(fn() => self::isFieldDisabled())
                            ->columnSpanFull(),

                        // ─── Muncul setelah tipe dipilih ─────────────────────
                        // Info box tipe yang dipilih
                        Placeholder::make('type_info_box')
                            ->label('')
                            ->content(function (Get $get) {
                                $slug = self::publicationTypeSlug($get);
                                if (!$slug) return new \Illuminate\Support\HtmlString('');

                                $map = [
                                    'jurnal' => [
                                        'icon'  => '📄',
                                        'label' => 'Jurnal / Artikel Ilmiah',
                                        'color' => '#1D4ED8',
                                        'bg'    => '#EFF6FF',
                                        'border' => '#BFDBFE',
                                        'id'    => 'DOI (Digital Object Identifier)',
                                        'desc'  => 'Karya jurnal memerlukan DOI sebagai identifier unik.',
                                    ],
                                    'buku' => [
                                        'icon'  => '📚',
                                        'label' => 'Buku',
                                        'color' => '#166534',
                                        'bg'    => '#F0FDF4',
                                        'border' => '#BBF7D0',
                                        'id'    => 'ISBN (International Standard Book Number)',
                                        'desc'  => 'Buku memerlukan ISBN sebagai identifier unik.',
                                    ],
                                    'opini' => [
                                        'icon'  => '✍️',
                                        'label' => 'Opini / Artikel Populer',
                                        'color' => '#92400E',
                                        'bg'    => '#FFFBEB',
                                        'border' => '#FDE68A',
                                        'id'    => 'Nama Media Publikasi',
                                        'desc'  => 'Opini memerlukan nama media tempat pertama kali diterbitkan.',
                                    ],
                                ];

                                $cfg = $map[$slug] ?? null;
                                if (!$cfg) return new \Illuminate\Support\HtmlString('');

                                return new \Illuminate\Support\HtmlString("
                                <div style='background:{$cfg['bg']};border:1px solid {$cfg['border']};border-left:4px solid {$cfg['color']};border-radius:8px;padding:12px 16px;font-size:13px;'>
                                    <div style='font-weight:700;color:{$cfg['color']};margin-bottom:4px;'>
                                        {$cfg['icon']} Tipe dipilih: {$cfg['label']}
                                    </div>
                                    <div style='color:#374151;'>
                                        Identifier yang dibutuhkan: <strong>{$cfg['id']}</strong><br>
                                        <span style='color:#6B7280;font-size:12px;'>{$cfg['desc']}</span>
                                    </div>
                                </div>
                            ");
                            })
                            ->visible(fn(Get $get) => filled($get('publication_type_id')))
                            ->columnSpanFull(),

                        // ─── STEP 2: Pilih status publikasi sebelumnya ────────
                        Radio::make('is_previously_published')
                            ->label('Apakah karya ini sudah pernah diterbitkan di platform atau media lain sebelumnya?')
                            ->options([
                                '0' => '🆕 Tidak — Ini adalah karya baru yang belum pernah dipublikasikan di mana pun',
                                '1' => '🌐 Ya — Karya ini sudah pernah dipublikasikan dan berstatus Open Access',
                            ])
                            ->descriptions([
                                '0' => 'Karya akan melalui proses review standar platform.',
                                '1' => 'Anda wajib mengisi detail publikasi sebelumnya. Karya harus berstatus Open Access di sumber aslinya.',
                            ])
                            ->required()
                            ->live()
                            ->default('0')
                            ->visible(fn(Get $get) => filled($get('publication_type_id')))
                            ->disabled(fn() => self::isFieldDisabled())
                            ->columnSpanFull(),

                        // ─── Info karya baru ──────────────────────────────────
                        Placeholder::make('new_work_info')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString("
                            <div style='background:#F0FDF4;border:1px solid #BBF7D0;border-radius:8px;padding:16px 20px;'>
                                <div style='font-size:14px;font-weight:600;color:#166534;margin-bottom:8px;'>✅ Karya Baru — Lanjutkan ke step berikutnya</div>
                                <p style='font-size:13px;color:#166534;margin:0;'>
                                    Karya Anda akan diproses melalui alur review standar Platform. Pastikan karya belum pernah dikirimkan ke platform atau jurnal lain secara bersamaan (<em>simultaneous submission</em>).
                                </p>
                            </div>
                        "))
                            ->visible(fn(Get $get) => filled($get('publication_type_id')) && $get('is_previously_published') === '0')
                            ->columnSpanFull(),

                        // ─────────────────────────────────────────────────────
                        // Detail Prior Publication — muncul jika pilih "Ya"
                        // ─────────────────────────────────────────────────────
                        Section::make('Detail Publikasi Sebelumnya')
                            ->description('Lengkapi informasi berikut dengan jujur dan akurat.')
                            ->icon('heroicon-o-link')
                            ->visible(fn(Get $get) => $get('is_previously_published') === '1')
                            ->columnSpanFull()
                            ->schema([

                                // Nama platform/penerbit
                                TextInput::make('prior_publisher_name')
                                    ->label('Nama Platform / Penerbit Sebelumnya')
                                    ->placeholder('Contoh: ResearchGate, Elsevier, Gramedia, Kompas')
                                    ->required(fn(Get $get) => $get('is_previously_published') === '1')
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-building-library')
                                    ->helperText('Wajib. Nama platform, jurnal, atau penerbit tempat karya pertama kali diterbitkan.')
                                    ->disabled(fn() => self::isFieldDisabled())
                                    ->columnSpan(1),

                                // URL publikasi sebelumnya
                                TextInput::make('prior_publisher_url')
                                    ->label('URL / Link Karya Sebelumnya')
                                    ->placeholder('https://doi.org/10.xxxx/... atau https://researchgate.net/...')
                                    ->url()
                                    ->required(fn(Get $get) => $get('is_previously_published') === '1')
                                    ->maxLength(500)
                                    ->prefixIcon('heroicon-o-link')
                                    ->helperText('Wajib. URL langsung ke halaman karya. Akan diverifikasi oleh admin.')
                                    ->suffixAction(
                                        \Filament\Actions\Action::make('open_prior_url')
                                            ->icon('heroicon-o-arrow-top-right-on-square')
                                            ->url(fn(Get $get) => $get('prior_publisher_url'))
                                            ->openUrlInNewTab()
                                            ->visible(fn(Get $get) => filled($get('prior_publisher_url')))
                                    )
                                    ->disabled(fn() => self::isFieldDisabled())
                                    ->columnSpan(1),

                                // ── DOI — khusus Jurnal ───────────────────────
                                TextInput::make('prior_identifier_value')
                                    ->label('DOI (Digital Object Identifier)')
                                    ->placeholder('10.1016/j.xxx.2024.01.001')
                                    ->helperText('Wajib untuk jurnal. Format: 10.xxxx/yyyyy — tanpa awalan "https://doi.org/"')
                                    ->prefix('https://doi.org/')
                                    ->maxLength(255)
                                    ->required(fn(Get $get) => $get('is_previously_published') === '1')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Set $set) => $set('prior_identifier_type', 'doi'))
                                    ->visible(fn(Get $get) => self::publicationTypeSlug($get) === 'jurnal' && $get('is_previously_published') === '1')
                                    ->disabled(fn() => self::isFieldDisabled())
                                    ->suffixAction(
                                        \Filament\Actions\Action::make('open_doi')
                                            ->icon('heroicon-o-arrow-top-right-on-square')
                                            ->url(fn(Get $get) => filled($get('prior_identifier_value'))
                                                ? 'https://doi.org/' . $get('prior_identifier_value')
                                                : null)
                                            ->openUrlInNewTab()
                                            ->visible(fn(Get $get) => filled($get('prior_identifier_value')))
                                    )
                                    ->columnSpan(1),

                                // ── ISBN — khusus Buku ────────────────────────
                                TextInput::make('prior_identifier_value')
                                    ->label('ISBN (International Standard Book Number)')
                                    ->placeholder('978-602-XXXX-XX-X')
                                    ->helperText('Wajib untuk buku. Format ISBN-13: 978-XXX-XXXX-XX-X')
                                    ->prefix('ISBN')
                                    ->maxLength(255)
                                    ->required(fn(Get $get) => $get('is_previously_published') === '1')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Set $set) => $set('prior_identifier_type', 'isbn'))
                                    ->visible(fn(Get $get) => self::publicationTypeSlug($get) === 'buku' && $get('is_previously_published') === '1')
                                    ->disabled(fn() => self::isFieldDisabled())
                                    ->columnSpan(1),

                                // ── Nama Media — khusus Opini ─────────────────
                                TextInput::make('prior_identifier_value')
                                    ->label('Nama Media / Portal Tempat Opini Diterbitkan')
                                    ->placeholder('Contoh: Kompas, Tempo, Detik.com, Kumparan')
                                    ->helperText('Wajib untuk opini. Sebutkan nama media tempat opini pertama kali diterbitkan.')
                                    ->maxLength(255)
                                    ->required(fn(Get $get) => $get('is_previously_published') === '1')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Set $set) => $set('prior_identifier_type', 'media_name'))
                                    ->visible(fn(Get $get) => self::publicationTypeSlug($get) === 'opini' && $get('is_previously_published') === '1')
                                    ->disabled(fn() => self::isFieldDisabled())
                                    ->columnSpan(1),

                                // Hidden — identifier type (doi/isbn/media_name)
                                TextInput::make('prior_identifier_type')->hidden()->dehydrated(),

                                // Tanggal terbit pertama
                                DatePicker::make('prior_published_date')
                                    ->label('Tanggal Pertama Diterbitkan')
                                    ->required(fn(Get $get) => $get('is_previously_published') === '1')
                                    ->maxDate(now())
                                    ->displayFormat('d F Y')
                                    ->helperText('Wajib. Tanggal publikasi pertama di platform/media sebelumnya.')
                                    ->disabled(fn() => self::isFieldDisabled())
                                    ->columnSpan(1),

                                // Lisensi OA
                                Select::make('origin_license')
                                    ->label('Lisensi Open Access di Sumber Asli')
                                    ->options([
                                        'CC BY 4.0'       => 'CC BY 4.0 — Atribusi (paling terbuka)',
                                        'CC BY-SA 4.0'    => 'CC BY-SA 4.0 — Atribusi-BerbagiSerupa',
                                        'CC BY-NC 4.0'    => 'CC BY-NC 4.0 — Atribusi-NonKomersial',
                                        'CC BY-NC-SA 4.0' => 'CC BY-NC-SA 4.0 — Atribusi-NonKomersial-BerbagiSerupa',
                                        'CC BY-ND 4.0'    => 'CC BY-ND 4.0 — Atribusi-TanpaModifikasi',
                                        'CC BY-NC-ND 4.0' => 'CC BY-NC-ND 4.0 — Atribusi-NonKomersial-TanpaModifikasi',
                                        'CC0 1.0'         => 'CC0 1.0 — Public Domain (tanpa syarat)',
                                        'other'           => 'Lisensi terbuka lainnya',
                                    ])
                                    ->required(fn(Get $get) => $get('is_previously_published') === '1')
                                    ->searchable()
                                    ->helperText('Wajib. Pilih lisensi CC yang berlaku di platform asal.')
                                    ->disabled(fn() => self::isFieldDisabled())
                                    ->columnSpan(1),

                                // Konfirmasi OA
                                Checkbox::make('is_open_access_origin')
                                    ->label('✅ Saya mengkonfirmasi bahwa karya ini berstatus Open Access di platform/penerbit asal dan saya berhak untuk mempublikasikan ulang karya ini.')
                                    ->required(fn(Get $get) => $get('is_previously_published') === '1')
                                    ->accepted()
                                    ->validationMessages(['accepted' => 'Anda harus mengkonfirmasi status Open Access karya ini.'])
                                    ->helperText('Wajib. Dengan mencentang ini, Anda menyatakan bahwa karya dapat dipublikasikan ulang secara legal.')
                                    ->disabled(fn() => self::isFieldDisabled())
                                    ->columnSpanFull(),

                                // Warning hukum
                                Placeholder::make('oa_warning')
                                    ->label('')
                                    ->content(new \Illuminate\Support\HtmlString("
                                    <div style='background:#FEF9C3;border:1px solid #FDE047;border-left:4px solid #EAB308;border-radius:6px;padding:12px 16px;font-size:13px;color:#713F12;'>
                                        ⚠️ <strong>Peringatan:</strong> Jika karya yang Anda ajukan tidak berstatus Open Access di sumber aslinya dan Anda tidak memiliki izin redistribusi dari penerbit, tindakan tersebut dapat merupakan <strong>pelanggaran hak cipta</strong> dan Anda dapat dikenakan sanksi sesuai <strong>UU No. 28 Tahun 2014 tentang Hak Cipta</strong> (Pasal 113 — ancaman pidana penjara paling lama 4 tahun dan/atau denda paling banyak Rp 1 miliar).
                                    </div>
                                "))
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MAIN CONFIGURE METHOD
    // ══════════════════════════════════════════════════════════════════════════

    public static function configure(Schema $schema): Schema
    {
        $record = $schema->getRecord();

        return $schema
            ->columns(1)
            ->components([
                Placeholder::make('status_banner')
                    ->label('Status Publikasi')
                    ->content(fn($record) => new \Illuminate\Support\HtmlString(
                        self::renderStatusBanner($record)
                    ))
                    ->visible(fn($record) => (bool) $record?->id)
                    ->columnSpanFull(),

                Wizard::make([

                    // ─── Step 0: Deklarasi & LOA ──────────────────────────
                    self::buildLoaStep(),

                    // ─── Step 1: Status Publikasi Sebelumnya ──────────────
                    self::buildPriorPublicationStep(),

                    // ─── Step 2: Informasi Publikasi ──────────────────────
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
                                        ->helperText('✅ Sudah dipilih di Step sebelumnya. Bisa diubah jika perlu.')
                                        ->disabled(fn() => self::isFieldDisabled())
                                        ->columnSpan(1),


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
                                        ->unique(
                                            table: 'publications',
                                            column: 'title',
                                            ignoreRecord: true
                                        )
                                        ->validationMessages([
                                            'unique' => 'Judul karya ilmiah ini sudah pernah digunakan. Silakan gunakan judul yang berbeda atau tambahkan penjelasan spesifik.',
                                        ])
                                        ->disabled(fn() => self::isFieldDisabled())
                                        ->columnSpanFull(),

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
                                        ->disabled(fn($record) => self::isContentDisabled($record)),

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
                                        ->disabled(fn($record) => self::isContentDisabled($record)),

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
                                        ->disabled(fn($record) => self::isContentDisabled($record)),

                                    Select::make('keywords')
                                        ->label('Keywords')
                                        ->searchPrompt('Ketik kata kunci...')
                                        ->noSearchResultsMessage('Kata kunci tidak ditemukan. Klik ＋ untuk menambahkan baru.')
                                        ->relationship('keywords', 'name', fn($query) => $query->orderBy('name'))
                                        ->multiple()->searchable()->preload()
                                        ->minItems(3)->maxItems(7)
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->required(fn($get) => self::publicationTypeSlug($get) === 'jurnal')
                                        ->createOptionForm(self::keywordCreateOptionForm('Keyword'))
                                        ->createOptionUsing(fn(array $data) => Keyword::create($data)->getKey())
                                        ->helperText('Wajib. Pilih minimal 3 dan maksimal 7 keyword.')
                                        ->disabled(fn() => self::isFieldDisabled())
                                        ->columnSpanFull(),

                                    Select::make('keywords')
                                        ->label('Tags')
                                        ->searchPrompt('Ketik tag...')
                                        ->noSearchResultsMessage('Tag tidak ditemukan. Klik ＋ untuk menambahkan baru.')
                                        ->relationship('keywords', 'name', fn($query) => $query->orderBy('name'))
                                        ->multiple()->searchable()->preload()->maxItems(3)
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'buku')
                                        ->required(false)
                                        ->createOptionForm(self::keywordCreateOptionForm('Tag'))
                                        ->createOptionUsing(fn(array $data) => Keyword::create($data)->getKey())
                                        ->helperText('Opsional. Maksimal 3 tag.')
                                        ->disabled(fn() => self::isFieldDisabled())
                                        ->columnSpanFull(),

                                    Select::make('keywords')
                                        ->label('Topik')
                                        ->searchPrompt('Ketik topik...')
                                        ->noSearchResultsMessage('Topik tidak ditemukan. Klik ＋ untuk menambahkan baru.')
                                        ->relationship('keywords', 'name', fn($query) => $query->orderBy('name'))
                                        ->multiple()->searchable()->preload()->maxItems(3)
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'opini')
                                        ->required(false)
                                        ->createOptionForm(self::keywordCreateOptionForm('Topik'))
                                        ->createOptionUsing(fn(array $data) => Keyword::create($data)->getKey())
                                        ->helperText('Opsional. Maksimal 3 topik.')
                                        ->disabled(fn() => self::isFieldDisabled())
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // ─── Step 3: Klasifikasi ───────────────────────────────
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
                                            TextInput::make('name')->label('Nama Kategori')->required()->maxLength(100)->live(onBlur: true)->unique(table: 'categories', column: 'name', ignoreRecord: true)->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                            TextInput::make('slug')->label('Slug')->required()->disabled()->dehydrated()->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'super_admin'])),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Category::create($data)->getKey())
                                        ->helperText('Pilih 1 kategori.')
                                        ->disabled(fn() => self::isFieldDisabled())->columnSpan(1),

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
                                            TextInput::make('name')->label('Nama Metode')->required()->maxLength(100)->live(onBlur: true)->unique(table: 'methods', column: 'name', ignoreRecord: true)->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                            TextInput::make('slug')->label('Slug')->required()->disabled()->dehydrated()->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'super_admin'])),
                                        ])
                                        ->createOptionUsing(fn(array $data) => Method::create($data)->getKey())
                                        ->helperText(fn($get) => match (self::publicationTypeSlug($get)) {
                                            'jurnal' => 'Wajib. Pilih metode penelitian.',
                                            'buku'   => 'Opsional.',
                                            default  => '',
                                        })
                                        ->disabled(fn() => self::isFieldDisabled())->columnSpan(1),

                                    Placeholder::make('method_info')
                                        ->label('')
                                        ->content('Opini tidak memerlukan klasifikasi metode penelitian.')
                                        ->visible(fn($get) => self::publicationTypeSlug($get) === 'opini')
                                        ->columnSpan(1),
                                ]),
                        ]),

                    // ─── Step 4: Penulis ───────────────────────────────────
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
                                                    if (self::isReviewer()) return true;
                                                    $items    = $component->getState();
                                                    $authorId = $items[$arguments['item']]['author_id'] ?? null;
                                                    if (!$authorId) return false;
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
                                        ->collapsed(false)
                                        ->collapseAllAction(fn(\Filament\Actions\Action $action) => $action->label('Ciutkan semua'))
                                        ->expandAllAction(fn(\Filament\Actions\Action $action) => $action->label('Buka semua'))
                                        ->itemLabel(function (array $state): ?string {
                                            $authorId = $state['author_id'] ?? null;
                                            if (!$authorId) return 'Penulis baru';
                                            $author = \App\Models\Author::find($authorId);
                                            if (!$author) return 'Penulis';
                                            $label = $author->name;
                                            if ($state['is_corresponding'] ?? false) $label .= ' · Corresponding';
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
                                                                ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
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
                                                    FileUpload::make('photo_path')->label('Foto Profil')->avatar()->disk('public')->directory('authors/photos')->visibility('public')->imageEditor()->circleCropper()->imageEditorMode(2)->maxSize(2048)->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])->helperText('JPG, PNG. Maks 2MB. Opsional.')->moveFiles()->extraAttributes(['class' => 'flex flex-col items-center justify-center']),
                                                    Grid::make()->columns(['default' => 1, 'md' => 2])->schema([
                                                        TextInput::make('name')->label('Nama Lengkap')->required()->maxLength(255)->placeholder('Contoh: Dr. John Doe, M.T.')->prefixIcon('heroicon-o-user')->helperText('Wajib untuk external author (tanpa akun).'),
                                                        TextInput::make('email')->label('Email')->email()->maxLength(255)->placeholder('john@example.com')->prefixIcon('heroicon-o-envelope')->unique(table: 'authors', column: 'email', ignoreRecord: true)->helperText('Opsional.'),
                                                    ]),
                                                    TextInput::make('affiliation')->label('Affiliasi / Institusi')->maxLength(255)->placeholder('Universitas / Organisasi')->prefixIcon('heroicon-o-building-office')->helperText('Opsional.'),
                                                    TextInput::make('orcid_id')->label('ORCID iD')->placeholder('0000-0000-0000-0000')->helperText('Format: 0000-0000-0000-0000')->maxLength(19)->regex('/^\d{4}-\d{4}-\d{4}-\d{3}[\dXx]$/')->validationMessages(['regex' => 'Format ORCID tidak valid. Gunakan format: 0000-0000-0000-0000'])->suffixAction(\Filament\Actions\Action::make('open_orcid')->icon('heroicon-o-arrow-top-right-on-square')->url(fn($get) => $get('orcid_id') ? 'https://orcid.org/' . $get('orcid_id') : null)->openUrlInNewTab()->visible(fn($get) => filled($get('orcid_id')))),
                                                    Textarea::make('bio')->label('Biografi')->rows(4)->maxLength(1000)->placeholder('Tulis bio singkat penulis...')->helperText('Opsional. Maks. 1000 karakter.'),
                                                    Select::make('user_id')->label('Hubungkan ke Akun Pengguna')->relationship(name: 'user', titleAttribute: 'name', modifyQueryUsing: fn($query) => $query->whereDoesntHave('author')->orderBy('name'))->getOptionLabelFromRecordUsing(fn(\App\Models\User $user) => "{$user->name} — {$user->email}")->searchable(['name', 'email'])->preload()->nullable()->placeholder('— Tidak terhubung (External Author) —')->prefixIcon('heroicon-o-link')->helperText('Opsional.')->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'super_admin']))->afterStateUpdated(function ($state, callable $set) {
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
                                            $state = array_values(array_filter($state, fn($row) => !empty($row['author_id'])));
                                            $hasCorresponding = collect($state)->contains(fn($row) => (bool) ($row['is_corresponding'] ?? false));
                                            if (!$hasCorresponding && count($state) > 0) $state[0]['is_corresponding'] = true;
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
                                        ->disabled(fn() => self::isFieldDisabled()),
                                ]),
                        ]),

                    // ─── Step 5: Finalisasi ────────────────────────────────
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
                                        ->image()->disk('public')->directory('publications/covers')->visibility('public')
                                        ->imageEditor()->imageEditorAspectRatios([null, '2:3'])->imageCropAspectRatio('2:3')
                                        ->imageResizeTargetWidth(600)->imageResizeTargetHeight(900)->imageResizeMode('cover')
                                        ->imagePreviewHeight('300')->maxSize(2048)
                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                        ->helperText('Format: JPG/PNG/WebP. Maks. 2MB. Rasio ideal 2:3.')
                                        ->disabled(fn() => self::isFieldDisabled())
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
                                        ->default('draft')->required()->live()
                                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                            if ($state === 'published' && blank($get('published_at'))) $set('published_at', now());
                                            if ($state !== 'published') $set('published_at', null);
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

                    // ─── Step 6: Preview ───────────────────────────────────
                    Step::make('Preview')
                        ->description('Cek tampilan sebelum simpan')
                        ->icon('heroicon-o-eye')
                        ->completedIcon('heroicon-o-check-circle')
                        ->schema([
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

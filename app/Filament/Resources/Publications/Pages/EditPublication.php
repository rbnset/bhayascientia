<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use App\Filament\Resources\PublicationVersionResource;
use App\Models\Author;
use App\Models\Publication;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class EditPublication extends EditRecord
{
    protected static string $resource = PublicationResource::class;

    // ─────────────────────────────────────────────────────────────
    // Mount
    // ─────────────────────────────────────────────────────────────

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (
            auth()->user()?->hasRole('author') &&
            in_array($this->record->status, PublicationResource::AUTHOR_LOCKED_STATUSES, true)
        ) {
            Notification::make()
                ->title('Tidak dapat mengedit publikasi')
                ->body('Publikasi berstatus "' . str($this->record->status)->headline() . '" tidak dapat diubah. Hubungi editor jika ada koreksi.')
                ->warning()
                ->persistent()
                ->send();

            $this->redirect(PublicationResource::getUrl('index'));
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers — Role
    // ─────────────────────────────────────────────────────────────

    protected function isReviewer(): bool
    {
        return (bool) auth()->user()?->hasRole('reviewer');
    }

    protected function isAuthor(): bool
    {
        return (bool) auth()->user()?->hasRole('author');
    }

    protected function shortTitle(): string
    {
        return Str::of((string) $this->record->title)->squish()->words(8, '…')->toString();
    }

    protected function latestVersion(): ?\App\Models\PublicationVersion
    {
        return $this->record->versions()->latest('version_number')->first();
    }

    protected function isOpini(): bool
    {
        return $this->record->publicationType?->slug === 'opini';
    }

    protected function authorRecipients()
    {
        $authorUserIds = $this->record->authors()
            ->pluck('authors.user_id')
            ->filter()->unique()->values();

        if ($authorUserIds->isEmpty()) return collect();

        return \App\Models\User::whereIn('id', $authorUserIds)->get();
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers — Review
    // ─────────────────────────────────────────────────────────────

    /**
     * ✅ Ambil review terbaru untuk publikasi ini.
     *    Mendukung dua jalur:
     *    1. Review biasa     → via publication_version_id (publikasi dengan manuskrip)
     *    2. Review opini     → via publication_id langsung (opini tanpa manuskrip)
     */
    protected function getLatestReviewForPublication(): ?\App\Models\Review
    {
        // Jalur 1: ada versi manuskrip → cari review via versi terbaru
        $latestVersion = $this->latestVersion();
        if ($latestVersion) {
            return \App\Models\Review::query()
                ->where('publication_version_id', $latestVersion->id)
                ->orderByDesc('id')
                ->first();
        }

        // Jalur 2: opini tanpa manuskrip → cari review via publication_id
        return \App\Models\Review::query()
            ->where('publication_id', $this->record->id)
            ->whereNull('publication_version_id')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * ✅ Cek apakah ada review untuk publikasi ini.
     *    Query langsung — tidak bergantung pada relasi ORM agar selalu akurat
     *    saat halaman pertama kali di-render.
     */
    protected function publicationHasAnyReview(): bool
    {
        // Cek via versi manuskrip (semua tipe publikasi yang punya manuskrip)
        $versionIds = $this->record->versions()->pluck('id');
        if ($versionIds->isNotEmpty()) {
            $hasViaVersion = \App\Models\Review::query()
                ->whereIn('publication_version_id', $versionIds)
                ->exists();
            if ($hasViaVersion) return true;
        }

        // Cek via publication_id langsung (opini tanpa manuskrip)
        return \App\Models\Review::query()
            ->where('publication_id', $this->record->id)
            ->whereNull('publication_version_id')
            ->exists();
    }

    /**
     * ✅ Reviewer: cek apakah ada versi baru yang belum direview olehnya
     */
    protected function hasNewerVersionToReview(): bool
    {
        if (!$this->isReviewer()) return false;

        $latestVersion = $this->latestVersion();
        if (!$latestVersion) return false;

        return !\App\Models\Review::query()
            ->where('publication_version_id', $latestVersion->id)
            ->where('reviewer_id', auth()->id())
            ->exists();
    }

    /**
     * ✅ Reviewer: cek apakah pernah mereview publikasi ini (versi manapun)
     */
    protected function reviewerHasEverReviewedThisPublication(): bool
    {
        // Cek via versi manuskrip
        $versionIds = $this->record->versions()->pluck('id');
        if ($versionIds->isNotEmpty()) {
            $reviewed = \App\Models\Review::query()
                ->whereIn('publication_version_id', $versionIds)
                ->where('reviewer_id', auth()->id())
                ->exists();
            if ($reviewed) return true;
        }

        // Cek opini tanpa manuskrip
        return \App\Models\Review::query()
            ->where('publication_id', $this->record->id)
            ->whereNull('publication_version_id')
            ->where('reviewer_id', auth()->id())
            ->exists();
    }

    /**
     * ✅ Reviewer: ambil review miliknya sendiri untuk publikasi ini (versi terbaru)
     */
    protected function getMyLatestReview(): ?\App\Models\Review
    {
        $latestVersion = $this->latestVersion();

        if ($latestVersion) {
            return \App\Models\Review::query()
                ->where('publication_version_id', $latestVersion->id)
                ->where('reviewer_id', auth()->id())
                ->orderByDesc('id')
                ->first();
        }

        return \App\Models\Review::query()
            ->where('publication_id', $this->record->id)
            ->whereNull('publication_version_id')
            ->where('reviewer_id', auth()->id())
            ->orderByDesc('id')
            ->first();
    }

    // ─────────────────────────────────────────────────────────────
    // Mutate before save
    // ─────────────────────────────────────────────────────────────

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->isReviewer()) {
            $filtered = ['status' => $data['status'] ?? $this->record->status];
            if (($filtered['status'] ?? null) === 'published') {
                $filtered['published_at'] = $data['published_at'] ?? $this->record->published_at;
            }
            return $filtered;
        }

        $title = trim($data['title'] ?? '');
        if (filled($title) && $title !== $this->record->title) {
            if (Publication::where('title', $title)->where('id', '!=', $this->record->id)->exists()) {
                Notification::make()
                    ->title('Judul sudah digunakan')
                    ->body('Tambahkan konteks spesifik (metode, lokasi, tahun) agar judul menjadi unik.')
                    ->danger()->persistent()->send();
                $this->halt();
            }
        }

        return $data;
    }

    // ─────────────────────────────────────────────────────────────
    // Handle record update
    // ─────────────────────────────────────────────────────────────

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (UniqueConstraintViolationException $e) {
            Notification::make()
                ->title('Gagal menyimpan')
                ->body('Data bertabrakan dengan entri lain. Periksa judul atau field unik lainnya.')
                ->danger()->persistent()->send();
            $this->halt();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // After save
    // ─────────────────────────────────────────────────────────────

    protected function afterSave(): void
    {
        if ($this->isReviewer()) {
            if (
                $this->record->status === 'published' &&
                ($this->record->wasChanged('status') || $this->record->wasChanged('published_at'))
            ) {
                $recipients = $this->authorRecipients();
                if ($recipients->isNotEmpty()) {
                    \Illuminate\Support\Facades\Notification::send(
                        $recipients,
                        new \App\Notifications\PublicationScheduledToPublish($this->record)
                    );
                }
            }
            return;
        }

        $creator = $this->record->creator ?? null;
        if (!$creator) return;

        $author = Author::firstOrCreate(
            ['user_id' => $creator->id],
            ['name' => $creator->name, 'email' => $creator->email, 'affiliation' => null]
        );

        $this->record->authors()->syncWithoutDetaching([
            $author->id => ['order' => 1, 'is_corresponding' => true],
        ]);

        \App\Models\Pivots\AuthorPublication::where('publication_id', $this->record->id)
            ->where('author_id', '!=', $author->id)
            ->update(['is_corresponding' => false]);
    }

    // ─────────────────────────────────────────────────────────────
    // Saved notification
    // ─────────────────────────────────────────────────────────────

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Publikasi berhasil diperbarui')
            ->body('"' . $this->shortTitle() . '" berhasil disimpan.');
    }

    // ─────────────────────────────────────────────────────────────
    // Header actions
    // ─────────────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            // ── Submit Manuskrip — author & draft ────────────────
            Action::make('submitManuscript')
                ->label(fn() => $this->isOpini() ? 'Submit dengan Manuskrip' : 'Submit Manuskrip')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn() => $this->record->status === 'draft' && !$this->isReviewer())
                ->modalHeading('Submit Manuskrip ke Reviewer')
                ->modalDescription(
                    '⚠️ Setelah dikirim, manuskrip tidak dapat diubah hingga reviewer memberi keputusan. ' .
                        'Pastikan judul, abstrak, penulis, dan kata kunci sudah lengkap.'
                )
                ->modalSubmitActionLabel('Kirim Sekarang')
                ->modalCancelActionLabel('Batal, Periksa Lagi')
                ->form([
                    FileUpload::make('pdf_file_path')
                        ->label('Manuscript (PDF)')
                        ->disk('public')
                        ->directory('publications/versions')
                        ->acceptedFileTypes(['application/pdf'])
                        ->required()
                        ->maxSize(10240)
                        ->helperText('Pastikan isi berkas sudah benar sebelum mengirim. Maksimal upload 10MB'),

                    Checkbox::make('confirm_reviewed')
                        ->label('Saya telah meninjau berkas PDF dan memastikan isinya sudah benar')
                        ->required()
                        ->accepted(),
                ])
                ->action(function (array $data) {
                    $this->record->versions()->create([
                        'pdf_file_path'  => $data['pdf_file_path'],
                        'version_number' => 1,
                        'submitted_at'   => now(),
                    ]);

                    $this->record->update(['status' => 'submitted']);

                    \Illuminate\Support\Facades\Notification::send(
                        \App\Models\User::role('reviewer')->get(),
                        new \App\Notifications\PublicationSubmitted($this->record)
                    );

                    Notification::make()
                        ->success()
                        ->title('Manuskrip berhasil dikirim')
                        ->body('"' . $this->shortTitle() . '" masuk antrian review.')
                        ->send();
                }),

            // ── Kirim Tanpa Manuskrip — HANYA opini, status draft ─
            Action::make('submitOpiniTanpaManuscript')
                ->label('Kirim Tanpa Manuskrip')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('warning')
                ->visible(
                    fn() => $this->record->status === 'draft'
                        && !$this->isReviewer()
                        && $this->isOpini()
                )
                ->modalHeading('Kirim Opini Tanpa Manuskrip?')
                ->modalDescription(new HtmlString(
                    '📝 Opini akan dikirim ke reviewer <strong>tanpa lampiran file PDF</strong>.<br><br>' .
                        'Reviewer akan menilai berdasarkan isi opini yang sudah kamu tulis di form. ' .
                        'Pastikan isi opini sudah lengkap sebelum melanjutkan.<br><br>' .
                        '⚠️ Setelah dikirim, opini tidak dapat diubah hingga reviewer memberi keputusan.'
                ))
                ->modalSubmitActionLabel('Ya, Kirim Sekarang')
                ->modalCancelActionLabel('Batal, Periksa Lagi')
                ->form([
                    Checkbox::make('confirm_no_manuscript')
                        ->label('Saya memahami bahwa opini ini dikirim tanpa file manuskrip PDF')
                        ->required()
                        ->accepted(),
                ])
                ->action(function () {
                    $this->record->update(['status' => 'submitted']);

                    \Illuminate\Support\Facades\Notification::send(
                        \App\Models\User::role('reviewer')->get(),
                        new \App\Notifications\PublicationSubmitted($this->record)
                    );

                    Notification::make()
                        ->success()
                        ->title('Opini berhasil dikirim')
                        ->body('"' . $this->shortTitle() . '" masuk antrian review tanpa manuskrip.')
                        ->send();
                }),

            // ── Lihat PDF Manuskrip ───────────────────────────────
            Action::make('previewPdf')
                ->label(function () {
                    $v = $this->latestVersion()?->version_number;
                    return $v ? "Lihat Manuskrip (v{$v})" : 'Lihat Manuskrip';
                })
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->visible(fn() => $this->record->versions()->exists())
                ->url(fn() => PublicationVersionResource::getUrl('pdf', [
                    'record' => $this->latestVersion(),
                ]))
                ->openUrlInNewTab(),

            // ── Lihat Detail Review ───────────────────────────────
            // ✅ FIXED: visible menggunakan publicationHasAnyReview() yang
            //    query langsung ke DB via version IDs + publication_id,
            //    sehingga akurat sejak halaman pertama kali di-render.
            //    Muncul di semua status (submitted, in_review, revision_required,
            //    accepted, published) selama review sudah ada.
            Action::make('lihatReview')
                ->label(function () {
                    // ✅ Hitung semua review: via versi + via publication_id langsung
                    $versionIds = $this->record->versions()->pluck('id');
                    $count = \App\Models\Review::query()
                        ->where(function ($q) use ($versionIds) {
                            if ($versionIds->isNotEmpty()) {
                                $q->whereIn('publication_version_id', $versionIds);
                            }
                            $q->orWhere(function ($q2) {
                                $q2->where('publication_id', $this->record->id)
                                    ->whereNull('publication_version_id');
                            });
                        })
                        ->count();

                    return $count > 1 ? "Lihat Review ({$count})" : 'Lihat Detail Review';
                })
                ->icon('heroicon-o-clipboard-document-list')
                ->color('info')
                ->visible(function () {
                    // ✅ Tidak tampil jika belum ada review
                    if (!$this->publicationHasAnyReview()) return false;

                    // ✅ Reviewer yang punya versi baru untuk direview
                    //    → sembunyikan, digantikan tombol 'reviewRevisiTerbaru'
                    if ($this->hasNewerVersionToReview()) return false;

                    return true;
                })
                ->action(function () {
                    // ── REVIEWER: ke review miliknya sendiri ─────────────
                    if ($this->isReviewer()) {
                        $myReview = $this->getMyLatestReview();

                        if (!$myReview) {
                            Notification::make()
                                ->title('Review belum tersedia')
                                ->body('Anda belum memiliki review untuk publikasi ini.')
                                ->warning()->send();
                            return;
                        }

                        $this->redirect(
                            \App\Filament\Resources\Reviews\ReviewResource::getUrl('edit', ['record' => $myReview->id])
                        );
                        return;
                    }

                    // ── AUTHOR & ADMIN: ke review terbaru ────────────────
                    $latestReview = $this->getLatestReviewForPublication();

                    if (!$latestReview) {
                        Notification::make()
                            ->title('Review sedang diproses')
                            ->body('Reviewer belum membuat catatan review. Harap tunggu notifikasi dari reviewer.')
                            ->info()
                            ->persistent()
                            ->send();
                        return;
                    }

                    if ($this->isAuthor()) {
                        // Author → view (read-only)
                        $this->redirect(
                            \App\Filament\Resources\Reviews\ReviewResource::getUrl('view', ['record' => $latestReview->id])
                        );
                        return;
                    }

                    // Admin → edit
                    $this->redirect(
                        \App\Filament\Resources\Reviews\ReviewResource::getUrl('edit', ['record' => $latestReview->id])
                    );
                }),

            // ── Review Naskah — reviewer, status submitted ────────
            Action::make('reviewManuscript')
                ->label('Review Naskah')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('primary')
                ->visible(function () {
                    if (!$this->isReviewer()) return false;
                    if ($this->record->status !== 'submitted') return false;

                    return !$this->reviewerHasEverReviewedThisPublication();
                })
                ->requiresConfirmation()
                ->modalHeading('Mulai Review Naskah?')
                ->modalDescription(fn() => new HtmlString(
                    '📄 <strong>' . e($this->record->title) . '</strong><br><br>' .
                        'Status publikasi akan berubah menjadi <strong>In Review</strong> dan ' .
                        'Anda akan diarahkan ke halaman review.'
                ))
                ->modalSubmitActionLabel('Ya, Mulai Review')
                ->modalCancelActionLabel('Batal')
                ->action(function () {
                    $this->record->update(['status' => 'in_review']);

                    $latestVersion = $this->latestVersion();

                    // Opini tanpa manuskrip
                    if (!$latestVersion) {
                        $existingReview = \App\Models\Review::query()
                            ->where('publication_id', $this->record->id)
                            ->whereNull('publication_version_id')
                            ->where('reviewer_id', auth()->id())
                            ->first();

                        if ($existingReview) {
                            $this->redirect(\App\Filament\Resources\Reviews\ReviewResource::getUrl('edit', ['record' => $existingReview->id]));
                            return;
                        }

                        $review = \App\Models\Review::create([
                            'publication_version_id' => null,
                            'publication_id'         => $this->record->id,
                            'reviewer_id'            => auth()->id(),
                        ]);

                        $recipients = $this->authorRecipients();
                        if ($recipients->isNotEmpty()) {
                            \Illuminate\Support\Facades\Notification::send(
                                $recipients,
                                new \App\Notifications\PublicationInReview($this->record)
                            );
                        }

                        Notification::make()
                            ->success()
                            ->title('Review dimulai')
                            ->body('Status berubah ke "In Review". Author telah dinotifikasi.')
                            ->send();

                        $this->redirect(\App\Filament\Resources\Reviews\ReviewResource::getUrl('edit', ['record' => $review->id]));
                        return;
                    }

                    // Review biasa (dengan manuskrip)
                    $existingReview = \App\Models\Review::query()
                        ->where('publication_version_id', $latestVersion->id)
                        ->where('reviewer_id', auth()->id())
                        ->first();

                    if ($existingReview) {
                        $this->redirect(\App\Filament\Resources\Reviews\ReviewResource::getUrl('edit', ['record' => $existingReview->id]));
                        return;
                    }

                    $review = \App\Models\Review::create([
                        'publication_version_id' => $latestVersion->id,
                        'reviewer_id'            => auth()->id(),
                    ]);

                    $recipients = $this->authorRecipients();
                    if ($recipients->isNotEmpty()) {
                        \Illuminate\Support\Facades\Notification::send(
                            $recipients,
                            new \App\Notifications\PublicationInReview($this->record)
                        );
                    }

                    Notification::make()
                        ->success()
                        ->title('Review dimulai')
                        ->body('Status berubah ke "In Review". Author telah dinotifikasi.')
                        ->send();

                    $this->redirect(\App\Filament\Resources\Reviews\ReviewResource::getUrl('edit', ['record' => $review->id]));
                }),

            // ── Review Revisi Terbaru — reviewer, ada versi baru ─
            Action::make('reviewRevisiTerbaru')
                ->label(fn() => 'Review Revisi Terbaru (v' . ($this->latestVersion()?->version_number ?? '-') . ')')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(function () {
                    if (!$this->isReviewer()) return false;
                    if (!$this->hasNewerVersionToReview()) return false;

                    return $this->reviewerHasEverReviewedThisPublication();
                })
                ->requiresConfirmation()
                ->modalHeading('Review Revisi Terbaru?')
                ->modalDescription(function () {
                    $latest = $this->latestVersion();
                    return new HtmlString(
                        '📄 <strong>' . e($this->record->title) . '</strong><br><br>' .
                            "Author telah mengirim revisi <strong>v{$latest?->version_number}</strong>. " .
                            'Klik lanjut untuk mulai mereview versi terbaru ini. ' .
                            'Status publikasi akan berubah ke <strong>In Review</strong>.'
                    );
                })
                ->modalSubmitActionLabel('Ya, Mulai Review')
                ->modalCancelActionLabel('Batal')
                ->action(function () {
                    $latestVersion = $this->latestVersion();
                    if (!$latestVersion) return;

                    $existingReview = \App\Models\Review::query()
                        ->where('publication_version_id', $latestVersion->id)
                        ->where('reviewer_id', auth()->id())
                        ->first();

                    if ($existingReview) {
                        $this->redirect(\App\Filament\Resources\Reviews\ReviewResource::getUrl('edit', ['record' => $existingReview->id]));
                        return;
                    }

                    $review = \App\Models\Review::create([
                        'publication_version_id' => $latestVersion->id,
                        'reviewer_id'            => auth()->id(),
                    ]);

                    $this->record->update(['status' => 'in_review']);

                    $recipients = $this->authorRecipients();
                    if ($recipients->isNotEmpty()) {
                        \Illuminate\Support\Facades\Notification::send(
                            $recipients,
                            new \App\Notifications\PublicationInReview($this->record)
                        );
                    }

                    Notification::make()
                        ->success()
                        ->title('Review revisi dimulai')
                        ->body('v' . $latestVersion->version_number . ' sedang Anda review. Author telah dinotifikasi.')
                        ->send();

                    $this->redirect(\App\Filament\Resources\Reviews\ReviewResource::getUrl('edit', ['record' => $review->id]));
                }),

            // ── Terbitkan Naskah — reviewer & admin, status accepted ─
            Action::make('publishNaskah')
                ->label('Terbitkan Naskah')
                ->icon('heroicon-o-rocket-launch')
                ->color('success')
                ->visible(
                    fn() => ($this->isReviewer() || !$this->isAuthor()) &&
                        $this->record->status === 'accepted'
                )
                ->modalHeading('Terbitkan Naskah')
                ->modalDescription(new HtmlString(
                    '🎉 Naskah ini sudah diterima dan siap untuk diterbitkan.<br><br>' .
                        'Tentukan tanggal dan waktu penerbitan. Naskah akan langsung dapat diakses publik ' .
                        'setelah status berubah ke <strong>Published</strong>.'
                ))
                ->modalSubmitActionLabel('Terbitkan Sekarang')
                ->modalCancelActionLabel('Batal')
                ->form([
                    \Filament\Forms\Components\DateTimePicker::make('published_at')
                        ->label('Tanggal & Waktu Terbit')
                        ->required()
                        ->default(now('Asia/Jakarta'))
                        ->timezone('Asia/Jakarta')
                        ->native(false)
                        ->helperText('Waktu dalam zona WIB (UTC+7). Biarkan sekarang untuk langsung terbit.'),

                    \Filament\Forms\Components\Checkbox::make('confirm_publish')
                        ->label('Saya yakin ingin menerbitkan naskah ini ke publik')
                        ->required()
                        ->accepted(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status'       => 'published',
                        'published_at' => $data['published_at'],
                    ]);

                    $recipients = $this->authorRecipients();
                    if ($recipients->isNotEmpty()) {
                        \Illuminate\Support\Facades\Notification::send(
                            $recipients,
                            new \App\Notifications\PublicationScheduledToPublish($this->record)
                        );
                    }

                    $tanggal = \Carbon\Carbon::parse($data['published_at'])
                        ->timezone('Asia/Jakarta')
                        ->locale('id')
                        ->isoFormat('D MMMM YYYY, HH:mm');

                    Notification::make()
                        ->success()
                        ->title('Naskah berhasil diterbitkan')
                        ->body('"' . $this->shortTitle() . '" sudah live sejak ' . $tanggal . ' WIB.')
                        ->send();

                    $this->redirect(request()->header('Referer') ?? PublicationResource::getUrl('edit', ['record' => $this->record]));
                }),

            // ── Lihat Halaman Publikasi — jika sudah published ────
            Action::make('lihatPublikasi')
                ->label('Lihat Halaman Publikasi')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->visible(fn() => $this->record->status === 'published')
                ->url(fn() => route('publikasi.show', ['slug' => $this->record->slug]))
                ->openUrlInNewTab(),

            // ── Upload Revisi — author & admin, revision_required ─
            Action::make('uploadNewVersion')
                ->label('Upload Revisi')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->visible(fn() => $this->record->status === 'revision_required' && !$this->isReviewer())
                ->modalHeading('Upload Revisi Manuskrip')
                ->modalDescription(function () {
                    if ($this->isOpini() && !$this->latestVersion()) {
                        return new HtmlString(
                            '📝 Opini ini sebelumnya dikirim <strong>tanpa manuskrip PDF</strong>.<br><br>' .
                                'Anda bisa upload PDF revisi sekarang, atau jika reviewer meminta revisi konten opini, ' .
                                'perbaiki isi opini di form lalu submit kembali tanpa file.<br><br>' .
                                '⚠️ Setelah dikirim, status akan kembali ke <strong>Submitted</strong> untuk direview ulang.'
                        );
                    }
                    return null;
                })
                ->modalSubmitActionLabel('Kirim Revisi')
                ->modalCancelActionLabel('Batal, Periksa Lagi')
                ->form([
                    FileUpload::make('pdf_file_path')
                        ->label('Revised Manuscript (PDF)')
                        ->disk('public')
                        ->directory('publications/versions')
                        ->acceptedFileTypes(['application/pdf'])
                        ->required(fn() => !($this->isOpini() && !$this->latestVersion()))
                        ->maxSize(10240)
                        ->helperText(
                            fn() => $this->isOpini() && !$this->latestVersion()
                                ? 'Opsional untuk opini. Kosongkan jika revisi hanya pada konten opini.'
                                : 'Pastikan isi berkas revisi sudah benar sebelum mengirim. Maksimal upload 10MB'
                        ),

                    Checkbox::make('confirm_reviewed')
                        ->label('Saya telah meninjau berkas PDF revisi dan memastikan semua catatan reviewer sudah diperbaiki')
                        ->required(fn() => !($this->isOpini() && !$this->latestVersion()))
                        ->accepted(),

                    Checkbox::make('confirm_opini_revision')
                        ->label('Saya telah memperbaiki isi opini di form sesuai catatan reviewer')
                        ->visible(fn() => $this->isOpini() && !$this->latestVersion())
                        ->accepted(),
                ])
                ->action(function (array $data) {
                    $hasPdf = filled($data['pdf_file_path'] ?? null);

                    if ($hasPdf) {
                        $nextVersion = ($this->record->versions()->max('version_number') ?? 0) + 1;

                        $this->record->versions()->create([
                            'pdf_file_path'  => $data['pdf_file_path'],
                            'version_number' => $nextVersion,
                            'submitted_at'   => now(),
                        ]);

                        $versionInfo = 'v' . $nextVersion;
                    } else {
                        $versionInfo = 'konten opini';
                        $nextVersion = null;
                    }

                    $this->record->update(['status' => 'submitted']);

                    $publication    = $this->record;
                    $reviewerIds    = $publication->reviews()
                        ->pluck('reviews.reviewer_id')
                        ->filter()->unique()->values();

                    if ($reviewerIds->isNotEmpty()) {
                        foreach (\App\Models\User::whereIn('id', $reviewerIds)->get() as $reviewer) {
                            $reviewIdToOpen = $publication->reviews()
                                ->where('reviews.reviewer_id', $reviewer->id)
                                ->orderByDesc('reviews.id')
                                ->value('reviews.id');

                            if ($hasPdf && $nextVersion !== null) {
                                $reviewer->notify(new \App\Notifications\AuthorSubmittedRevision(
                                    publication: $publication,
                                    newVersionNumber: $nextVersion,
                                    reviewIdToOpen: $reviewIdToOpen,
                                ));
                            } else {
                                $reviewer->notify(new \App\Notifications\PublicationSubmitted($publication));
                            }
                        }
                    } else {
                        // Fallback: belum ada reviewer sebelumnya → broadcast ke semua reviewer
                        \Illuminate\Support\Facades\Notification::send(
                            \App\Models\User::role('reviewer')->get(),
                            new \App\Notifications\PublicationSubmitted($publication)
                        );
                    }

                    Notification::make()
                        ->success()
                        ->title('Revisi berhasil dikirim')
                        ->body('"' . $this->shortTitle() . '" (' . $versionInfo . ') dikirim ke reviewer.')
                        ->send();
                }),
        ];
    }
}

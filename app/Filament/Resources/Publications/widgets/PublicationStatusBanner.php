<?php

namespace App\Filament\Resources\Publications\Widgets;

use App\Models\Publication;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class PublicationStatusBanner extends Widget
{
    protected string $view = 'filament.publications.widgets.publication-status-banner';

    // Full width (span seluruh kolom grid widget). [web:58]
    protected int | string | array $columnSpan = 'full';

    public ?Model $record = null;

    public function getViewData(): array
    {
        /** @var Publication|null $publication */
        $publication = $this->record instanceof Publication ? $this->record : null;

        if (! $publication) {
            return ['show' => false];
        }

        $status = (string) $publication->status;

        // Ambil review terakhir (buat pesan dinamis: "direview oleh siapa")
        $latestReview = $publication->versions()
            ->with(['reviews.reviewer'])
            ->get()
            ->flatMap(fn($version) => $version->reviews)
            ->sortByDesc('created_at')
            ->first();

        $latestReviewerName = $latestReview?->reviewer?->name;
        $latestReviewDecision = $latestReview?->decision;
        $latestReviewedAt = $latestReview?->created_at;

        // Pesan dinamis per status
        $config = match ($status) {
            'draft' => [
                'variant' => 'gray',
                'title' => 'Masih draft',
                'message' => 'Publikasi masih draft. Lengkapi data dan kirim naskah saat sudah siap.',
            ],
            'submitted' => [
                'variant' => 'info',
                'title' => 'Sudah dikirim (Submitted)',
                'message' => 'Naskah sudah berhasil diunggah dan dikirim. Tim reviewer akan mulai meninjau.',
            ],
            'in_review' => [
                'variant' => 'warning',
                'title' => 'Sedang direview',
                'message' => 'Publikasi sedang dalam proses peninjauan. Tunggu hasil keputusan reviewer.',
            ],
            'revision_required' => [
                'variant' => 'warning-strong',
                'title' => 'Perlu revisi',
                'message' => 'Publikasi perlu direvisi. Segera lakukan revisi dan upload ulang versi terbaru.',
            ],
            'accepted' => [
                'variant' => 'success',
                'title' => 'Diterima (Accepted)',
                'message' => 'Publikasi telah diterima. Lanjutkan ke tahap finalisasi/publikasi sesuai alur.',
            ],
            'rejected' => [
                'variant' => 'danger',
                'title' => 'Ditolak (Rejected)',
                'message' => 'Publikasi ditolak. Silakan cek catatan reviewer untuk detail alasannya.',
            ],
            'published' => [
                'variant' => 'success-strong',
                'title' => 'Sudah terbit (Published)',
                'message' => 'Publikasi sudah diterbitkan dan dapat diakses sesuai pengaturan.',
            ],
            default => [
                'variant' => 'gray',
                'title' => 'Status tidak dikenali',
                'message' => 'Periksa konfigurasi status publikasi.',
            ],
        };

        return [
            'show' => true,
            'status' => $status,

            'variant' => $config['variant'],
            'title' => $config['title'],
            'message' => $config['message'],

            'latestReviewerName' => $latestReviewerName,
            'latestReviewDecision' => $latestReviewDecision,
            'latestReviewedAt' => $latestReviewedAt,

            'publishedAt' => $publication->published_at,
        ];
    }
}

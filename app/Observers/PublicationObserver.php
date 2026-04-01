<?php

namespace App\Observers;

use App\Jobs\PreGeneratePdfCache;
use App\Models\Publication;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class PublicationObserver
{
    /**
     * Dipanggil saat publikasi di-update (termasuk saat status berubah ke 'published').
     * Juga dipanggil saat versi PDF baru diupload.
     */
    public function updated(Publication $publication): void
    {
        // Hanya proses jika status berubah ke 'published'
        if (
            $publication->wasChanged('status') &&
            $publication->status === 'published'
        ) {
            Log::info("PublicationObserver: dispatching PDF cache job", [
                'publication_id' => $publication->id,
                'slug'           => $publication->slug,
            ]);

            // Dispatch dengan delay 3 detik agar DB commit selesai dulu
            PreGeneratePdfCache::dispatch($publication->id)->delay(now()->addSeconds(3));

            // ── Kirim email & notifikasi ke semua author ──────────────────
            $this->notifyAuthorsPublished($publication);
        }
    }

    /**
     * Dipanggil saat publikasi baru dibuat langsung dengan status published.
     */
    public function created(Publication $publication): void
    {
        if ($publication->status === 'published') {
            PreGeneratePdfCache::dispatch($publication->id)->delay(now()->addSeconds(5));

            // ── Kirim email & notifikasi ke semua author ──────────────────
            $this->notifyAuthorsPublished($publication);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Kumpulkan semua User yang terdaftar sebagai author di publikasi ini,
     * lalu kirim email ManuscriptPublished + in-app notification.
     */
    private function notifyAuthorsPublished(Publication $publication): void
    {
        // Pastikan published_at terisi (edge case jika belum di-set)
        if (empty($publication->published_at)) {
            $publication->updateQuietly(['published_at' => now()]);
            $publication->refresh();
        }

        // Load relasi yang dibutuhkan
        $publication->loadMissing(['authors.user', 'publicationType']);

        // Ambil User unik dari author yang punya akun
        $recipients = $publication->authors
            ->filter(fn($author) => $author->user_id && $author->user)
            ->map(fn($author) => $author->user)
            ->unique('id')
            ->values();

        if ($recipients->isEmpty()) {
            Log::warning('PublicationObserver: no author recipients with user account', [
                'publication_id' => $publication->id,
            ]);
            return;
        }

        // ── Email ─────────────────────────────────────────────────────────
        foreach ($recipients as $user) {
            if (empty($user->email)) continue;

            try {
                Mail::to($user->email, $user->name)
                    ->queue(new \App\Mail\ManuscriptPublished($publication));

                Log::info('PublicationObserver: queued ManuscriptPublished email', [
                    'publication_id' => $publication->id,
                    'recipient'      => $user->email,
                ]);
            } catch (\Throwable $e) {
                Log::error('PublicationObserver: failed to queue email', [
                    'publication_id' => $publication->id,
                    'recipient'      => $user->email,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        // ── In-app notification ───────────────────────────────────────────
        try {
            Notification::send(
                $recipients,
                new \App\Notifications\PublicationScheduledToPublish($publication)
            );
        } catch (\Throwable $e) {
            Log::error('PublicationObserver: failed to send in-app notification', [
                'publication_id' => $publication->id,
                'error'          => $e->getMessage(),
            ]);
        }
    }
}

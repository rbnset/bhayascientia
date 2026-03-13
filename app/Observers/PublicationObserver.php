<?php

namespace App\Observers;

use App\Jobs\PreGeneratePdfCache;
use App\Models\Publication;
use Illuminate\Support\Facades\Log;

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
        }
    }

    /**
     * Dipanggil saat publikasi baru dibuat langsung dengan status published.
     */
    public function created(Publication $publication): void
    {
        if ($publication->status === 'published') {
            PreGeneratePdfCache::dispatch($publication->id)->delay(now()->addSeconds(5));
        }
    }
}

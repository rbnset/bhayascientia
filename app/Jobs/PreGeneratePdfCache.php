<?php

namespace App\Jobs;

use App\Models\Publication;
use App\Models\PublicationVersion;
use App\Support\PdfStamper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PreGeneratePdfCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Timeout 5 menit — cukup untuk PDF besar + GhostScript
    public int $timeout = 300;

    // Jangan retry jika gagal (GhostScript error biasanya permanen)
    public int $tries = 1;

    public function __construct(
        private readonly int $publicationId
    ) {}

    public function handle(): void
    {
        $publication = Publication::with(['publicationType', 'versions'])
            ->find($this->publicationId);

        if (!$publication) {
            Log::warning("PreGeneratePdfCache: publication {$this->publicationId} not found");
            return;
        }

        $latestVersion = $publication->versions()
            ->whereNotNull('pdf_file_path')
            ->orderBy('version_number', 'desc')
            ->first();

        if (!$latestVersion) {
            Log::warning("PreGeneratePdfCache: no version for publication {$this->publicationId}");
            return;
        }

        $filePath = $this->cleanPath($latestVersion->pdf_file_path);

        if (!Storage::disk('public')->exists($filePath)) {
            Log::warning("PreGeneratePdfCache: file not found", ['path' => $filePath]);
            return;
        }

        $absolutePath = Storage::disk('public')->path($filePath);
        $cacheDir     = storage_path('app/pdf_cache');

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        // Generate untuk GUEST dan USER — keduanya punya watermark berbeda
        foreach (['guest' => true, 'user' => false] as $label => $isGuest) {
            $cacheKey  = 'stamped_' . $latestVersion->id
                . '_' . $label
                . '_' . ($latestVersion->updated_at?->timestamp ?? 0);
            $cachePath = $cacheDir . '/' . $cacheKey . '.pdf';

            // Skip jika cache sudah ada
            if (file_exists($cachePath)) {
                Log::info("PreGeneratePdfCache: cache already exists [{$label}]", ['key' => $cacheKey]);
                continue;
            }

            try {
                Log::info("PreGeneratePdfCache: generating [{$label}]", [
                    'publication_id' => $this->publicationId,
                    'version_id'     => $latestVersion->id,
                ]);

                $latestVersion->setRelation('publication', $publication);
                $content = PdfStamper::stamp($absolutePath, $latestVersion, $isGuest);

                file_put_contents($cachePath, $content);

                // Hapus cache lama versi sebelumnya
                $prefix = 'stamped_' . $latestVersion->id . '_' . $label . '_';
                foreach (glob($cacheDir . '/' . $prefix . '*.pdf') as $file) {
                    if (basename($file, '.pdf') !== $cacheKey) {
                        @unlink($file);
                    }
                }

                Log::info("PreGeneratePdfCache: done [{$label}]", ['size' => strlen($content)]);
            } catch (\Throwable $e) {
                Log::error("PreGeneratePdfCache: failed [{$label}]", [
                    'publication_id' => $this->publicationId,
                    'error'          => $e->getMessage(),
                ]);
            }
        }
    }

    private function cleanPath(string $path): string
    {
        $path = preg_replace('#^/?storage/#', '', $path);
        $path = preg_replace('#^/?public/#', '', $path);
        return ltrim($path, '/');
    }
}

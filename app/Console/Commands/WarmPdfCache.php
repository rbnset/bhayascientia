<?php

namespace App\Console\Commands;

use App\Jobs\PreGeneratePdfCache;
use App\Models\Publication;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class WarmPdfCache extends Command
{
    protected $signature   = 'pdf:warm-cache {--force : Regenerate semua cache meski sudah ada}';
    protected $description = 'Pre-generate PDF cache untuk semua publikasi yang sudah published';

    public function handle(): int
    {
        $publications = Publication::where('status', 'published')
            ->whereHas('versions', fn($q) => $q->whereNotNull('pdf_file_path'))
            ->with(['versions' => fn($q) => $q->whereNotNull('pdf_file_path')->orderBy('version_number', 'desc')])
            ->get();

        $this->info("Ditemukan {$publications->count()} publikasi published.");

        $cacheDir   = storage_path('app/pdf_cache');
        $dispatched = 0;
        $skipped    = 0;

        foreach ($publications as $pub) {
            $version = $pub->versions->first();
            if (!$version) continue;

            $ts       = $version->updated_at?->timestamp ?? 0;
            $hasGuest = file_exists($cacheDir . '/stamped_' . $version->id . '_guest_' . $ts . '.pdf');
            $hasUser  = file_exists($cacheDir . '/stamped_' . $version->id . '_user_' . $ts . '.pdf');

            if (!$this->option('force') && $hasGuest && $hasUser) {
                $skipped++;
                continue;
            }

            PreGeneratePdfCache::dispatch($pub->id);
            $dispatched++;
            $this->line("  → Dispatched: [{$pub->id}] {$pub->title}");
        }

        $this->info("Dispatched: {$dispatched} jobs | Skipped (cache sudah ada): {$skipped}");
        $this->info("Jalankan: php83 artisan queue:work --stop-when-empty");

        return self::SUCCESS;
    }
}

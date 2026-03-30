<?php

namespace App\Console\Commands;

use App\Models\Publication;
use App\Models\Category;
use App\Models\PublicationType;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature   = 'sitemap:generate';
    protected $description = 'Generate sitemap.xml untuk SEO';

    public function handle()
    {
        $sitemap = Sitemap::create();

        // ── Halaman Statis ─────────────────────────────────────
        $sitemap->add(
            Url::create('/publikasi')
                ->setPriority(1.0)
                ->setChangeFrequency('daily')
        );
        $sitemap->add(
            Url::create('/beranda')
                ->setPriority(0.8)
                ->setChangeFrequency('monthly')
        );
        $sitemap->add(
            Url::create('/tentang')
                ->setPriority(0.6)
                ->setChangeFrequency('monthly')
        );
        $sitemap->add(
            Url::create('/kontak')
                ->setPriority(0.5)
                ->setChangeFrequency('monthly')
        );
        $sitemap->add(
            Url::create('/publikasi/trending')
                ->setPriority(0.7)
                ->setChangeFrequency('daily')
        );

        // ── Kategori ───────────────────────────────────────────
        Category::withCount([
            'publications' => fn($q) =>
            $q->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
        ])
            ->having('publications_count', '>', 0)
            ->each(function ($category) use ($sitemap) {
                $sitemap->add(
                    Url::create('/publikasi/kategori/' . $category->slug)
                        ->setPriority(0.7)
                        ->setChangeFrequency('weekly')
                        ->setLastModificationDate($category->updated_at)
                );
            });

        // ── Semua Publikasi ────────────────────────────────────
        Publication::where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->each(function ($pub) use ($sitemap) {
                $sitemap->add(
                    Url::create('/publikasi/' . $pub->slug)
                        ->setLastModificationDate($pub->updated_at)
                        ->setPriority(0.9)
                        ->setChangeFrequency('weekly')
                );
            });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('✅ Sitemap berhasil digenerate: ' . public_path('sitemap.xml'));
        $this->info('Total URL: ' . substr_count(file_get_contents(public_path('sitemap.xml')), '<url>'));
    }
}

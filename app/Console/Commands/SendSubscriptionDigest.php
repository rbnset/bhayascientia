<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionDigestMail;
use App\Models\Publication;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionDigest extends Command
{
    protected $signature   = 'subscription:send-digest {type : instant|daily|weekly_new|weekly_popular|monthly_popular}';
    protected $description = 'Kirim email digest ke semua subscriber aktif';

    public function handle(): void
    {
        $type = $this->argument('type');

        $this->info("Memulai pengiriman digest: {$type}");

        $subscribers = Subscription::with('user')
            ->active()
            ->byNotificationType($type)
            ->get();

        if ($subscribers->isEmpty()) {
            $this->info('Tidak ada subscriber untuk tipe ini.');
            return;
        }

        $this->info("Ditemukan {$subscribers->count()} subscriber.");

        $sent   = 0;
        $skipped = 0;

        foreach ($subscribers as $subscription) {
            // Skip jika user tidak ada
            if (!$subscription->user) {
                $skipped++;
                continue;
            }

            // Cek rate limit untuk instant
            if (!$subscription->canSendEmail()) {
                $this->line("Skip {$subscription->user->email} — limit harian tercapai.");
                $skipped++;
                continue;
            }

            // Ambil publikasi sesuai preferensi
            $publications = $this->getPublications($subscription, $type);

            if ($publications->isEmpty()) {
                $this->line("Skip {$subscription->user->email} — tidak ada publikasi baru.");
                $skipped++;
                continue;
            }

            try {
                Mail::to($subscription->user->email)
                    ->send(new SubscriptionDigestMail(
                        subscription: $subscription,
                        publications: $publications,
                        digestType: $type,
                        periodLabel: $this->getPeriodLabel($type),
                    ));

                $subscription->update(['last_sent_at' => now()]);
                $subscription->incrementEmailCount();

                $sent++;
                $this->info("✓ Terkirim ke {$subscription->user->email} ({$publications->count()} publikasi)");
            } catch (\Exception $e) {
                Log::error('Gagal kirim subscription digest', [
                    'user_id'    => $subscription->user_id,
                    'email'      => $subscription->user->email,
                    'type'       => $type,
                    'error'      => $e->getMessage(),
                ]);
                $this->error("✗ Gagal kirim ke {$subscription->user->email}: {$e->getMessage()}");
                $skipped++;
            }
        }

        $this->info("Selesai. Terkirim: {$sent}, Dilewati: {$skipped}");
    }

    private function getPublications(Subscription $subscription, string $type)
    {
        $query = Publication::with(['publicationType', 'categories', 'authors'])
            ->where('status', 'published')
            ->whereIn('publication_type_id', function ($q) use ($subscription) {
                $q->select('id')
                    ->from('publication_types')
                    ->whereIn('slug', $subscription->types ?? []);
            })
            ->whereHas('categories', function ($q) use ($subscription) {
                $q->whereIn('categories.id', $subscription->categories ?? []);
            });

        // Filter berdasarkan periode
        $query = match ($type) {
            'instant'         => $query->where('published_at', '>=', now()->subHours(1)),
            'daily'           => $query->where('published_at', '>=', now()->subDay()),
            'weekly_new'      => $query->orderBy('published_at', 'desc')
                ->where('published_at', '>=', now()->subWeek()),
            'weekly_popular'  => $query->orderBy('views_count', 'desc')
                ->where('published_at', '>=', now()->subWeek()),
            'monthly_popular' => $query->orderBy('views_count', 'desc')
                ->where('published_at', '>=', now()->subMonth()),
            default           => $query->where('published_at', '>=', now()->subDay()),
        };

        return $query->limit(10)->get();
    }

    private function getPeriodLabel(string $type): string
    {
        return match ($type) {
            'instant'         => now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
            'daily'           => 'Hari Ini, ' . now()->setTimezone('Asia/Jakarta')->format('d M Y'),
            'weekly_new'      => 'Minggu Ini (' . now()->startOfWeek()->format('d M') . ' – ' . now()->endOfWeek()->format('d M Y') . ')',
            'weekly_popular'  => 'Minggu Lalu (' . now()->subWeek()->startOfWeek()->format('d M') . ' – ' . now()->subWeek()->endOfWeek()->format('d M Y') . ')',
            'monthly_popular' => 'Bulan ' . now()->setTimezone('Asia/Jakarta')->translatedFormat('F Y'),
            default           => now()->format('d M Y'),
        };
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Review;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoRejectExpiredRevisions extends Command
{
    protected $signature = 'reviews:auto-reject-expired';
    protected $description = 'Otomatis reject review yang revision_deadline-nya sudah terlewat';

    public function handle(): void
    {
        $expired = Review::query()
            ->where('decision', 'revision_required')
            ->whereNotNull('revision_deadline')
            ->where('revision_deadline', '<', now())
            ->get();

        foreach ($expired as $review) {
            $review->update(['decision' => 'rejected']);

            Log::info("Review #{$review->id} otomatis di-reject karena revision_deadline terlewat.", [
                'review_id'         => $review->id,
                'revision_deadline' => $review->revision_deadline,
            ]);
        }

        $this->info("Total {$expired->count()} review otomatis di-reject.");
    }
}

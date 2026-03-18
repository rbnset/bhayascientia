<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PublicationHelperTrait;
use App\Models\Publication;
use App\Models\PublicationType;
use Illuminate\Http\Request;

class PublicationTrendingController extends Controller
{
    use PublicationHelperTrait;

    public function trending(Request $request)
    {
        $period   = $request->query('period', 7);
        $typeSlug = $request->query('type', 'all');

        if (!in_array($period, [7, 30])) {
            $period = 7;
        }

        $daysAgo          = (int) $period;
        $publicationTypes = PublicationType::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);

        $query = Publication::with(['authors.user', 'publicationType', 'categories'])
            ->withCount([
                'viewLogs as recent_views' => function ($query) use ($daysAgo) {
                    $query->where('created_at', '>=', now()->subDays($daysAgo));
                },
                'downloadLogs as recent_downloads' => function ($query) use ($daysAgo) {
                    $query->where('created_at', '>=', now()->subDays($daysAgo));
                },
            ])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if ($typeSlug !== 'all') {
            $query->whereHas('publicationType', function ($q) use ($typeSlug) {
                $q->where('slug', $typeSlug)->where('is_active', true);
            });
        }

        $trendingPublications = $query
            ->orderByRaw('(recent_views + recent_downloads * 2) DESC')
            ->orderByDesc('recent_downloads')
            ->orderByDesc('recent_views')
            ->orderByDesc('published_at')
            ->take(20)
            ->get()
            ->filter(fn($pub) => $pub->recent_views > 0 || $pub->recent_downloads > 0)
            ->take(10)
            ->values()
            ->map(function ($pub) {
                $pubType  = $pub->publicationType?->name ?? 'Publikasi';
                $coverUrl = $this->getCoverUrl($pub);

                $words    = array_filter(explode(' ', $pub->title));
                $initials = '';
                foreach (array_slice($words, 0, 2) as $word) {
                    $initials .= mb_strtoupper(mb_substr(trim($word), 0, 1));
                }
                if (empty($initials)) {
                    $initials = mb_strtoupper(mb_substr($pub->title, 0, 2));
                }

                $firstAuthor = $pub->authors->isNotEmpty()
                    ? ($pub->authors->first()->name ?? 'Unknown')
                    : 'Anonymous';

                // ✅ FIX: Hapus 'v' => time() — ini yang membunuh cache placeholder
                // Cache key tidak pernah hit karena time() selalu berbeda tiap request
                $placeholderUrl = route('placeholder.cover', [
                    'initials' => $initials,
                    'type'     => $pubType,
                    'title'    => $pub->title,
                    'category' => $pub->category_name,
                    'author'   => $firstAuthor,
                ]);

                // ✅ Hitung trending_score sekali di sini — konsisten dengan urutan DB
                $trendingScore = $pub->recent_views + ($pub->recent_downloads * 2);

                return [
                    'id'               => $pub->id,
                    'title'            => $pub->title,
                    'slug'             => $pub->slug,
                    'cover_url'        => $coverUrl,
                    'placeholder_url'  => $placeholderUrl,
                    'initials'         => $initials,
                    'category'         => $pub->category_name,
                    'formatted_date'   => $pub->formatted_date,
                    'publication_type' => $pubType,
                    'type'             => $pubType,
                    'type_slug'        => $pub->publicationType?->slug ?? 'publikasi',
                    'detail_url'       => route('publikasi.show', $pub->slug),
                    // ✅ Semua key score konsisten — dipakai blade untuk sort
                    'trending_score'   => $trendingScore,
                    'views_count'      => $pub->recent_views,
                    'download_count'   => $pub->recent_downloads,
                    'recent_views'     => $pub->recent_views,
                    'recent_downloads' => $pub->recent_downloads,
                    'authors'          => $pub->authors->take(6)->map(fn($author) => [
                        'id'       => $author->id,
                        'name'     => $author->name,
                        'photo'    => $author->photo_url,
                        'initials' => $author->initials,
                    ])->toArray(),
                    'total_authors'    => $pub->authors->count(),
                ];
            });

        // Type stats
        $typeStats = [];
        foreach ($publicationTypes as $type) {
            $count = $trendingPublications->where('type_slug', $type->slug)->count();
            if ($count > 0) {
                $typeStats[] = [
                    'slug'  => $type->slug,
                    'name'  => $type->name,
                    'count' => $count,
                ];
            }
        }

        return view('pages.publication.trending', compact(
            'trendingPublications',
            'publicationTypes',
            'period',
            'typeSlug',
            'typeStats'
        ));
    }
}

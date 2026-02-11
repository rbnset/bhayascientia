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
        $period = $request->query('period', 7);
        $typeSlug = $request->query('type', 'all');

        if (!in_array($period, [7, 30])) {
            $period = 7;
        }

        $daysAgo = (int) $period;
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
                }
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
            ->orderByRaw('recent_views + recent_downloads * 2 DESC')
            ->orderByDesc('recent_downloads')
            ->orderByDesc('recent_views')
            ->orderByDesc('published_at')
            ->take(50)
            ->get()
            ->filter(fn($pub) => $pub->recent_views > 0 || $pub->recent_downloads > 0)
            ->values()
            ->map(function ($pub) {
                return [
                    'id' => $pub->id,
                    'title' => $pub->title,
                    'slug' => $pub->slug,
                    'cover_url' => $this->getCoverUrl($pub),
                    'category' => $pub->category_name,
                    'formatted_date' => $pub->formatted_date,
                    'type' => $pub->publicationType->name ?? 'Publikasi',
                    'type_slug' => $pub->publicationType->slug ?? 'publikasi',
                    'detail_url' => route('publikasi.show', $pub->slug),
                    'trending_score' => $pub->recent_views + $pub->recent_downloads * 2,
                    'recent_views' => $pub->recent_views,
                    'recent_downloads' => $pub->recent_downloads,
                    'authors' => $pub->authors->take(6)->map(fn($author) => [
                        'id' => $author->id,
                        'name' => $author->name,
                        'photo' => $author->photo_url,
                        'initials' => $author->initials,
                    ])->toArray(),
                    'total_authors' => $pub->authors->count(),
                ];
            });

        // Type stats
        $typeStats = [];
        foreach ($publicationTypes as $type) {
            $count = $trendingPublications->where('type_slug', $type->slug)->count();
            if ($count > 0) {
                $typeStats[] = [
                    'slug' => $type->slug,
                    'name' => $type->name,
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

<?php

namespace App\Http\Controllers\Publication;

use App\Actions\Author\GetBestAuthorsAction as AuthorGetBestAuthorsAction;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PublicationHelperTrait;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\Category;
use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicationIndexController extends Controller
{
    use PublicationHelperTrait;

    public function __construct(private AuthorGetBestAuthorsAction $getBestAuthorsAction) {}

    public function index(Request $request)
    {
        $showTour = ! request()->cookie('has_seen_index_tour');

        $publicationTypes = PublicationType::with('content')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);

        if ($publicationTypes->isEmpty()) {
            return view('pages.publication.index', [
                'latestPublications'  => collect(),
                'publicationTypes'    => $publicationTypes,
                'selectedType'        => null,
                'bestAuthors'         => collect(),
                'popularPublications' => collect(),
                'featuredPublication' => null,
                'featuredTypeContent' => null,
                'categories'          => collect(),
                'years'               => collect(),
                'topKeywords'         => collect(),
                'filterSort'          => 'latest',
                'searchQuery'         => null,
                'showTour'            => $showTour,
            ]);
        }

        $selectedType = $request->query('type', $publicationTypes->first()->slug);
        $filterSort   = $request->query('sort', 'latest');
        $searchQuery  = null;

        if (!$publicationTypes->contains('slug', $selectedType)) {
            $selectedType = $publicationTypes->first()->slug;
        }

        $currentType = $publicationTypes->firstWhere('slug', $selectedType);

        $featuredTypeContent = null;
        if ($currentType && $currentType->content) {
            $featuredTypeContent = [
                'title'            => $currentType->content->title ?? $currentType->name,
                'cover_url'        => $this->getTypeContentCover($currentType->content),
                'category'         => $currentType->name,
                'publication_type' => $currentType->name,
                'type'             => $currentType->name,
                'abstract'         => $currentType->content->description,
                'download_count'   => 0,
                'detail_url'       => '',
                'slug'             => $currentType->slug,
            ];
        }

        $categories = Category::whereHas('publications', function ($query) use ($selectedType) {
            $query->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereHas('publicationType', fn($q) => $q->where('slug', $selectedType)->where('is_active', true));
        })
            ->withCount(['publications' => function ($query) use ($selectedType) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->whereHas('publicationType', fn($q) => $q->where('slug', $selectedType)->where('is_active', true));
            }])
            ->orderBy('name')
            ->get();

        $years = Publication::selectRaw('YEAR(published_at) as year')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('publicationType', fn($q) => $q->where('slug', $selectedType)->where('is_active', true))
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year');

        $topKeywords = Keyword::whereHas('publications', function ($query) use ($selectedType) {
            $query->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereHas('publicationType', fn($q) => $q->where('slug', $selectedType)->where('is_active', true));
        })
            ->withCount(['publications' => function ($query) use ($selectedType) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->whereHas('publicationType', fn($q) => $q->where('slug', $selectedType)->where('is_active', true));
            }])
            ->orderByDesc('publications_count')
            ->limit(20)
            ->get();

        $publicationsQuery = Publication::with('authors.user', 'publicationType', 'categories')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('publicationType', fn($q) => $q->where('slug', $selectedType)->where('is_active', true));

        switch ($filterSort) {
            case 'popular':
                $publicationsQuery->withCount('downloadLogs')->orderByDesc('download_logs_count');
                break;
            case 'oldest':
                $publicationsQuery->orderBy('published_at', 'asc');
                break;
            case 'title':
                $publicationsQuery->orderBy('title', 'asc');
                break;
            default:
                $publicationsQuery->orderBy('published_at', 'desc');
                break;
        }

        $publications       = $publicationsQuery->take(6)->get();
        $latestPublications = $publications->map(function ($pub) {
            $pubType = $pub->publicationType?->name ?? 'Publikasi';

            return [
                'id'               => $pub->id,
                'title'            => $pub->title,
                'slug'             => $pub->slug,
                'cover_url'        => $this->getCoverUrl($pub),
                'category'         => $pub->category_name,
                'publication_type' => $pubType,
                'formatted_date'   => $pub->formatted_date,
                'status'           => $pub->publicationType?->requires_review ? 'Peer-reviewed' : 'Terverifikasi',
                'type'             => $pubType,
                'detail_url'       => route('publikasi.show', $pub->slug),
                'authors'          => $pub->authors->take(6)->map(fn($a) => [
                    'id'       => $a->id,
                    'name'     => $a->name,
                    'photo'    => $a->photo_url,
                    'initials' => $a->initials,
                ])->toArray(),
                'total_authors'    => $pub->authors->count(),
            ];
        })->toArray();

        $bestAuthors = $this->getBestAuthorsAction->execute($selectedType, 6);

        // ✅ FIX: viewLogs pakai viewed_at bukan created_at
        // Relasi viewLogs → tabel publication_view_logs, kolom timestamp = viewed_at
        $popularPubs = Publication::with(['authors.user', 'publicationType', 'categories'])
            ->withCount([
                'viewLogs as total_views',       // ← tanpa filter waktu = semua waktu
                'downloadLogs as total_downloads',
            ])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('publicationType', fn($q) => $q->where('slug', $selectedType)->where('is_active', true))
            ->orderByRaw('(total_views + total_downloads * 2) DESC')
            ->take(6)
            ->get();

        $featuredPublication = null;
        if (!$featuredTypeContent && $popularPubs->first()) {
            $featuredPub     = $popularPubs->first();
            $featuredPubType = $featuredPub->publicationType?->name ?? 'Publikasi';

            $featuredPublication = [
                'id'               => $featuredPub->id,
                'title'            => $featuredPub->title,
                'slug'             => $featuredPub->slug,
                'cover_url'        => $this->getCoverUrl($featuredPub),
                'category'         => $featuredPub->category_name,
                'publication_type' => $featuredPubType,
                'type'             => $featuredPubType,
                'abstract'         => Str::limit(strip_tags($featuredPub->abstract ?? ''), 120),
                'download_count'   => (int) $featuredPub->total_downloads,
                'detail_url'       => route('publikasi.show', $featuredPub->slug),
            ];
        }

        $popularPublications = $popularPubs->map(function ($pub) {
            $pubType       = $pub->publicationType?->name ?? 'Publikasi';
            $trendingScore = (int) $pub->total_views + ((int) $pub->total_downloads * 2);

            return [
                'id'               => $pub->id,
                'title'            => $pub->title,
                'slug'             => $pub->slug,
                'cover_url'        => $this->getCoverUrl($pub),
                'category'         => $pub->category_name ?? ($pub->categories->first()?->name ?? 'Umum'),
                'publication_type' => $pubType,
                'formatted_date'   => $pub->formatted_date
                    ?? ($pub->published_at?->locale('id')->isoFormat('D MMMM YYYY') ?? ''),
                'download_count'   => (int) $pub->total_downloads,
                'views_count'      => (int) $pub->total_views,
                'trending_score'   => $trendingScore,
                'detail_url'       => route('publikasi.show', $pub->slug),
                'authors'          => $pub->authors->take(6)->map(fn($a) => [
                    'id'       => $a->id,
                    'name'     => $a->name,
                    'photo'    => $a->photo_url,
                    'initials' => $a->initials,
                ])->toArray(),
                'total_authors'    => $pub->authors->count(),
            ];
        });

        return view('pages.publication.index', compact(
            'latestPublications',
            'publicationTypes',
            'selectedType',
            'bestAuthors',
            'popularPublications',
            'featuredPublication',
            'featuredTypeContent',
            'categories',
            'years',
            'topKeywords',
            'filterSort',
            'searchQuery',
            'showTour',
        ));
    }
}

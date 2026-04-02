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
                'selectedType'        => 'all',
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

        $selectedType = $request->query('type', 'all');
        $filterSort   = $request->query('sort', 'latest');
        $searchQuery  = null;

        // Validasi: harus 'all' atau slug yang valid
        if ($selectedType !== 'all' && !$publicationTypes->contains('slug', $selectedType)) {
            $selectedType = 'all';
        }

        $currentType = $selectedType !== 'all'
            ? $publicationTypes->firstWhere('slug', $selectedType)
            : null;

        if ($selectedType === 'all') {
            $featuredTypeContent = (object) [
                'title'           => 'Koleksi Karya Populer Terpadu dan Terpercaya',
                'description'     => 'Akses berbagai karya pilihan yang mencakup buku, jurnal ilmiah, dan artikel opini yang disusun secara terintegrasi untuk mendukung kebutuhan literasi, referensi akademik, serta pengembangan wawasan secara komprehensif.',
                'image_url'       => asset('images/featured-all.jpg'),
                'publicationType' => null,
            ];
        } elseif ($currentType && $currentType->content) {
            $featuredTypeContent = (object) [
                'title'           => $currentType->content->title ?? $currentType->name,
                'image_url'       => $this->getTypeContentCover($currentType->content),
                'description'     => $currentType->content->description,
                'publicationType' => $currentType,
            ];
        } else {
            $featuredTypeContent = null;
        }

        $categories = Category::whereHas('publications', function ($query) use ($selectedType) {
            $query->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->when(
                    $selectedType !== 'all',
                    fn($q) => $q->whereHas(
                        'publicationType',
                        fn($q2) => $q2->where('slug', $selectedType)->where('is_active', true)
                    )
                );
        })
            ->withCount(['publications' => function ($query) use ($selectedType) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->when(
                        $selectedType !== 'all',
                        fn($q) => $q->whereHas(
                            'publicationType',
                            fn($q2) => $q2->where('slug', $selectedType)->where('is_active', true)
                        )
                    );
            }])
            ->orderBy('name')
            ->get();

        $years = Publication::selectRaw('YEAR(published_at) as year')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when(
                $selectedType !== 'all',
                fn($q) => $q->whereHas(
                    'publicationType',
                    fn($q2) => $q2->where('slug', $selectedType)->where('is_active', true)
                )
            )
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year');

        $topKeywords = Keyword::whereHas('publications', function ($query) use ($selectedType) {
            $query->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->when(
                    $selectedType !== 'all',
                    fn($q) => $q->whereHas(
                        'publicationType',
                        fn($q2) => $q2->where('slug', $selectedType)->where('is_active', true)
                    )
                );
        })
            ->withCount(['publications' => function ($query) use ($selectedType) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->when(
                        $selectedType !== 'all',
                        fn($q) => $q->whereHas(
                            'publicationType',
                            fn($q2) => $q2->where('slug', $selectedType)->where('is_active', true)
                        )
                    );
            }])
            ->orderByDesc('publications_count')
            ->limit(20)
            ->get();

        $publicationsQuery = Publication::with('authors.user', 'publicationType', 'categories')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when(
                $selectedType !== 'all',
                fn($q) => $q->whereHas(
                    'publicationType',
                    fn($q2) => $q2->where('slug', $selectedType)->where('is_active', true)
                )
            );

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

        $bestAuthors = $this->getBestAuthorsAction->execute(
            $selectedType !== 'all' ? $selectedType : null,
            6
        );

        $popularPubs = Publication::with(['authors.user', 'publicationType', 'categories'])
            ->withCount([
                'viewLogs as total_views',
                'downloadLogs as total_downloads',
            ])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when(
                $selectedType !== 'all',
                fn($q) => $q->whereHas(
                    'publicationType',
                    fn($q2) => $q2->where('slug', $selectedType)->where('is_active', true)
                )
            )
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
        ))->with([
            'seoTitle'       => 'Publikasi Ilmiah Kepolisian Indonesia — DABRAKA',
            'seoDescription' => 'Browse ribuan publikasi ilmiah, jurnal, buku, dan opini dari insan Bhayangkara dan akademisi Indonesia. Akses gratis di DABRAKA.',
        ]);
    }
}

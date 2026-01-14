<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublikasiController extends Controller
{
    /**
     * Display browse/discovery page with all publications
     */
    public function index(Request $request)
    {
        $filters = $this->extractFilters($request);

        // TODO: Query publications
        // $publications = Publication::query()
        //     ->applyFilters($filters)
        //     ->latest()
        //     ->paginate(12)
        //     ->withQueryString();

        return view('pages.publication.index', [
            'filters' => $filters,
            // 'publications' => $publications,
        ]);
    }

    /**
     * Display publications organized by categories
     */
    public function categories(Request $request)
    {
        $selectedCategory = $request->get('category');

        // TODO: Get categories with publication count
        // $categories = [
        //     ['name' => 'Technology', 'slug' => 'technology', 'count' => 124, 'icon' => '💻'],
        //     ['name' => 'Science', 'slug' => 'science', 'count' => 98, 'icon' => '🔬'],
        //     ['name' => 'Health', 'slug' => 'health', 'count' => 87, 'icon' => '💊'],
        //     ['name' => 'Education', 'slug' => 'education', 'count' => 76, 'icon' => '📚'],
        //     ['name' => 'Engineering', 'slug' => 'engineering', 'count' => 65, 'icon' => '⚙️'],
        //     ['name' => 'Business', 'slug' => 'business', 'count' => 54, 'icon' => '💼'],
        // ];

        // if ($selectedCategory) {
        //     $publications = Publication::where('category', $selectedCategory)
        //         ->latest()
        //         ->paginate(12);
        // }

        return view('pages.publication.categories', [
            'selectedCategory' => $selectedCategory,
            // 'categories' => $categories,
            // 'publications' => $publications ?? null,
        ]);
    }

    /**
     * Display trending/popular publications
     */
    public function trending(Request $request)
    {
        $period = $request->get('period', 'week'); // week, month, year, all-time

        // TODO: Query trending publications
        // $publications = Publication::query()
        //     ->when($period === 'week', fn($q) => $q->where('created_at', '>=', now()->subWeek()))
        //     ->when($period === 'month', fn($q) => $q->where('created_at', '>=', now()->subMonth()))
        //     ->when($period === 'year', fn($q) => $q->where('created_at', '>=', now()->subYear()))
        //     ->orderByDesc('views_count')
        //     ->orderByDesc('citations_count')
        //     ->paginate(12);

        return view('pages.publication.trending', [
            'period' => $period,
            // 'publications' => $publications,
        ]);
    }

    /**
     * Display user's personal library (favorites + history + saved)
     */
    public function library(Request $request)
    {
        $tab = $request->get('tab', 'favorites'); // favorites, history, saved

        // TODO: Get user's library items based on tab
        // $items = match($tab) {
        //     'favorites' => auth()->user()->favoritePublications()->paginate(12),
        //     'history' => auth()->user()->readingHistory()->paginate(12),
        //     'saved' => auth()->user()->savedPublications()->paginate(12),
        //     default => collect([]),
        // };

        // $stats = [
        //     'favorites' => auth()->user()->favoritePublications()->count(),
        //     'history' => auth()->user()->readingHistory()->count(),
        //     'saved' => auth()->user()->savedPublications()->count(),
        // ];

        return view('pages.publication.library', [
            'tab' => $tab,
            // 'items' => $items,
            // 'stats' => $stats,
        ]);
    }

    /**
     * Display single publication detail
     */
    public function show(int $id)
    {
        // TODO: Get publication detail
        // $publication = Publication::with(['authors', 'relatedPublications'])
        //     ->findOrFail($id);

        // Track view
        // $publication->increment('views_count');

        return view('pages.publication.show', [
            'id' => $id,
            // 'publication' => $publication,
        ]);
    }

    /**
     * Extract and validate filters from request
     */
    private function extractFilters(Request $request): array
    {
        return $request->only([
            'search',
            'category',
            'year',
            'year_from',
            'year_to',
            'author',
            'sort',
            'type',
        ]);
    }
}

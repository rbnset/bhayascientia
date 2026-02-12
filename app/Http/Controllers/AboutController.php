<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\User;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AboutController extends Controller
{
    /**
     * Display the about page with dynamic statistics
     */
    public function index()
    {
        // ✅ Get real stats from database with caching (5 minutes)
        $stats = Cache::remember('about_page_stats', now()->addMinutes(5), function () {
            return [
                // ✅ Use published() scope from Publication model
                'publications' => Publication::published()->count(),

                // ✅ Total registered users
                'users' => User::count(),

                // ✅ Total authors/writers
                'authors' => Author::count(),

                // ✅ Total categories
                'categories' => Category::count(),

                // ✅ Bonus: Additional stats (optional)
                'total_views' => \DB::table('publication_view_logs')->count(),
                'total_downloads' => \DB::table('download_logs')->count(),
            ];
        });

        return view('pages.about', compact('stats'));
    }
}

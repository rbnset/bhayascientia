<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\User;
use App\Models\Author;
use App\Models\Category;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AboutController extends Controller
{
    public function index()
    {
        // ✅ Stats dengan cache 5 menit
        $stats = Cache::remember('about_page_stats', now()->addMinutes(5), function () {
            return [
                'publications'    => Publication::published()->count(),
                'users'           => User::count(),
                'authors'         => Author::count(),
                'categories'      => Category::count(),
                'total_views'     => DB::table('publication_view_logs')->count(),
                'total_downloads' => DB::table('download_logs')->count(),
            ];
        });

        // ✅ Team data dengan cache 10 menit (jarang berubah)
        $teamData = Cache::remember('about_page_team', now()->addMinutes(10), function () {
            return [
                'leadership'  => TeamMember::active()->byLevel('leadership')->orderBy('order')->get(),
                'management'  => TeamMember::active()->byLevel('management')->orderBy('order')->get(),
                'departments' => TeamMember::active()->byLevel('department')->orderBy('order')->get(),
            ];
        });

        $leadership  = $teamData['leadership'];
        $management  = $teamData['management'];
        $departments = $teamData['departments'];

        return view('pages.about', compact(
            'stats',
            'leadership',
            'management',
            'departments'
        ));
    }
}

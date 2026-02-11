<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthorController extends Controller
{
    /**
     * Display the author's profile with their publications
     * Support both Author model and User model
     */
    public function show($identifier)
    {
        $author = null;
        $user = null;
        $isUserProfile = false;

        // Try to find in Author table first (by ID)
        if (is_numeric($identifier)) {
            $author = Author::with('user')->find($identifier);
        }

        // If not found, try to find in User table
        if (!$author) {
            $user = User::find($identifier);
            if ($user) {
                $isUserProfile = true;
                // Check if user has author profile
                $author = $user->authorProfile ?? null;
            }
        }

        // If still not found, return 404
        if (!$author && !$user) {
            abort(404, 'Author not found');
        }

        // Prepare data based on author type
        if ($isUserProfile && $user) {
            $name = $user->name;
            $email = $user->email;
            $bio = $user->bio ?? null;
            $affiliation = $user->affiliation ?? $user->job_title ?? null;
            $photoUrl = $user->photo_url;

            // Get publications through author profile
            if ($author) {
                $publicationsQuery = $author->publications();
            } else {
                // If user doesn't have author profile, return empty data
                return view('author.profile', [
                    'user' => $user,
                    'author' => null,
                    'name' => $name,
                    'email' => $email,
                    'bio' => $bio,
                    'affiliation' => $affiliation,
                    'photoUrl' => $photoUrl,
                    'publications' => collect()->paginate(12),
                    'totalPublications' => 0,
                    'totalViews' => 0,
                    'totalDownloads' => 0,
                    'coAuthors' => collect(),
                    'isUserProfile' => $isUserProfile,
                ]);
            }
        } else {
            $name = $author->name;
            $email = $author->email;
            $bio = $author->bio;
            $affiliation = $author->affiliation;
            $photoUrl = $author->photo_url;
            $user = $author->user; // Get related user if exists
            $publicationsQuery = $author->publications();
        }

        // ✅ Get publications with pagination
        $publications = $publicationsQuery
            ->with(['publicationType', 'categories', 'versions'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        // ✅ Get publication IDs for stats (avoid repeated queries)
        $publicationIds = $author->publications()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->pluck('publications.id')
            ->toArray();

        // ✅ Count statistics
        $totalPublications = count($publicationIds);

        // ✅ Total Views - Count from view logs table
        $totalViews = 0;
        if (!empty($publicationIds)) {
            $totalViews = DB::table('publication_view_logs')
                ->whereIn('publication_id', $publicationIds)
                ->count();
        }

        // ✅ Total Downloads - Count from download logs table
        $totalDownloads = 0;
        if (!empty($publicationIds)) {
            $totalDownloads = DB::table('download_logs')
                ->whereIn('publication_id', $publicationIds)
                ->count();
        }

        // ✅ Get co-authors (authors who collaborated with this author)
        $coAuthors = collect();
        if ($author) {
            $coAuthors = Author::whereHas('publications', function ($query) use ($author) {
                $query->whereIn('publications.id', function ($subQuery) use ($author) {
                    $subQuery->select('publication_id')
                        ->from('author_publication')
                        ->where('author_id', $author->id);
                })
                    ->where('publications.status', 'published')
                    ->whereNotNull('publications.published_at')
                    ->where('publications.published_at', '<=', now());
            })
                ->where('id', '!=', $author->id)
                ->withCount(['publications' => function ($query) {
                    $query->where('status', 'published')
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now());
                }])
                ->limit(6)
                ->get();
        }

        return view('author.profile', [
            'author' => $author,
            'user' => $user,
            'name' => $name,
            'email' => $email,
            'bio' => $bio,
            'affiliation' => $affiliation,
            'photoUrl' => $photoUrl,
            'publications' => $publications,
            'totalPublications' => $totalPublications,
            'totalViews' => $totalViews,
            'totalDownloads' => $totalDownloads,
            'coAuthors' => $coAuthors,
            'isUserProfile' => $isUserProfile,
        ]);
    }
}

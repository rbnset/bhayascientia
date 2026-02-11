<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PublicationHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicationLibraryController extends Controller
{
    use PublicationHelperTrait;

    public function library(Request $request)
    {
        $activeTab = $request->query('tab', 'favorites');

        if (!in_array($activeTab, ['favorites', 'history', 'saved'])) {
            $activeTab = 'favorites';
        }

        if (!Auth::check()) {
            return view('pages.publication.library', [
                'publications' => collect(),
                'stats' => ['favorites' => 0, 'history' => 0, 'saved' => 0],
                'activeTab' => $activeTab,
                'requiresLogin' => true,
                'user' => null,
            ]);
        }

        $user = Auth::user();

        // Stats
        $stats = [
            'favorites' => $user->favoritePublications()->count(),
            'history' => $user->readPublications()->count(),
            'saved' => $user->savedPublications()->count(),
        ];

        $publications = collect();

        switch ($activeTab) {
            case 'favorites':
                $publications = $user->favoritePublications()
                    ->with(['authors.user', 'publicationType', 'categories'])
                    ->whereHas('publicationType')
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->orderBy('user_favorite_publications.created_at', 'desc')
                    ->get();
                break;
            case 'history':
                $publications = $user->readPublications()
                    ->with(['authors.user', 'publicationType', 'categories'])
                    ->whereHas('publicationType')
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->orderBy('user_read_publications.last_read_at', 'desc')
                    ->get();
                break;
            case 'saved':
                $publications = $user->savedPublications()
                    ->with(['authors.user', 'publicationType', 'categories'])
                    ->whereHas('publicationType')
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->orderBy('user_saved_publications.created_at', 'desc')
                    ->get();
                break;
        }

        $publications = $publications->map(function ($pub) use ($activeTab) {
            $authorsText = $pub->authors->take(2)->pluck('name')->implode(', ');
            if ($pub->authors->count() > 2) {
                $authorsText .= ' . . . +' . ($pub->authors->count() - 2) . ' lainnya';
            }

            $actionTime = match ($activeTab) {
                'favorites' => $pub->pivot?->created_at
                    ? (is_string($pub->pivot->created_at)
                        ? \Carbon\Carbon::parse($pub->pivot->created_at)->diffForHumans()
                        : $pub->pivot->created_at->diffForHumans()
                    )
                    : 'Ditambahkan baru-baru ini',
                'history' => $pub->pivot?->last_read_at
                    ? (is_string($pub->pivot->last_read_at)
                        ? \Carbon\Carbon::parse($pub->pivot->last_read_at)->diffForHumans()
                        : $pub->pivot->last_read_at->diffForHumans()
                    )
                    : 'Dibaca baru-baru ini',
                'saved' => $pub->pivot?->created_at
                    ? (is_string($pub->pivot->created_at)
                        ? \Carbon\Carbon::parse($pub->pivot->created_at)->diffForHumans()
                        : $pub->pivot->created_at->diffForHumans()
                    )
                    : 'Disimpan baru-baru ini',
                default => 'Unknown'
            };

            return [
                'id' => $pub->id,
                'title' => $pub->title,
                'slug' => $pub->slug,
                'cover_url' => $this->getCoverUrl($pub),
                'category' => $pub->category_name,
                'formatted_date' => $pub->formatted_date,
                'type' => $pub->publicationType->name ?? 'Publikasi',
                'detail_url' => route('publikasi.show', $pub->slug),
                'action_time' => $actionTime,
                'authors_text' => $authorsText ?: 'Unknown',
                'authors' => $pub->authors->take(6)->map(fn($author) => [
                    'id' => $author->id,
                    'name' => $author->name,
                    'photo' => $author->photo_url,
                    'initials' => $author->initials,
                ])->toArray(),
                'total_authors' => $pub->authors->count(),
            ];
        });

        return view('pages.publication.library', compact('publications', 'stats', 'activeTab'));
    }
}

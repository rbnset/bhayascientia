<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PublicationHelperTrait;
use App\Models\Publication;
use App\Models\DownloadLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PublicationCoreController extends Controller
{
    use PublicationHelperTrait;

    public function show($slug)
    {
        $publication = Publication::with([
            'authors.user',
            'publicationType',
            'categories',
            'keywords',
            'versions' => fn($query) => $query->orderBy('version_number', 'desc')
        ])
        ->where('slug', $slug)
        ->where('status', 'published')
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->firstOrFail();

        $latestVersion = $publication->versions->first();
        $fileSize = null;
        $fileSizeFormatted = null;

        if ($latestVersion && $latestVersion->pdf_file_path) {
            $filePath = $this->cleanPath($latestVersion->pdf_file_path);
            if (Storage::disk('public')->exists($filePath)) {
                $fileSizeBytes = Storage::disk('public')->size($filePath);
                $fileSize = $fileSizeBytes;
                $fileSizeFormatted = $this->formatFileSize($fileSizeBytes);
            }
        }

        $downloadCount = $publication->downloadLogs()->where('publication_id', $publication->id)->count();
        $viewsCount = $publication->viewLogs()->where('publication_id', $publication->id)->count();

        $this->logPublicationView($publication);

        // Map authors dengan support User dan Author profile
        $authors = $publication->authors->map(function ($author) {
            $userData = $author->user;
            return [
                'id' => $author->id,
                'user_id' => $author->user_id,
                'name' => $author->name,
                'initials' => $author->initials,
                'photo' => $author->photo_url,
                'photo_url' => $author->photo_url,
                'affiliation' => $author->affiliation ?? ($userData ? ($userData->job_title ?? $userData->organization ?? '-') : '-'),
                'bio' => $author->bio ?? ($userData ? $userData->bio : null),
                'short_bio' => $author->short_bio,
                'email' => $author->email,
                'is_corresponding' => $author->pivot->is_corresponding ?? false,
                // Add profile routing support
                'profile_type' => $author->user_id ? 'user' : 'author',
                'profile_id' => $author->user_id ?? $author->id,
            ];
        });

        return view('pages.publication.show', [
            'publication' => $publication,
            'formatted_date' => $publication->published_at?->locale('id_ID')->isoFormat('D MMMM YYYY'),
            'category' => $publication->categories->first()?->name ?? 'Umum',
            'keywords' => $publication->keywords->pluck('name')->toArray(),
            'cover_url' => $this->getCoverUrl($publication),
            'authors' => $authors,
            'latest_version' => $latestVersion,
            'file_size' => $fileSize,
            'file_size_formatted' => $fileSizeFormatted,
            'download_count' => $downloadCount,
            'views_count' => $viewsCount,
        ]);
    }

    public function download($slug

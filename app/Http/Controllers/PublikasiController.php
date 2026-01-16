<?php

namespace App\Http\Controllers;

use App\Models\PublicationType;
use Illuminate\Http\Request;

class PublikasiController extends Controller
{
    /**
     * Display a listing of publications.
     */
    public function index(Request $request)
    {
        // Get active publication types from database
        $publicationTypes = PublicationType::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);

        // Get selected type from query string or default to first type
        $selectedType = $request->query('type', $publicationTypes->first()?->slug);

        // TODO: Query publications based on selected type
        // Untuk sementara kita buat array kosong dulu
        $latestPublications = [];

        return view('pages.publication.index', compact('publicationTypes', 'latestPublications', 'selectedType'));
    }

    /**
     * Display categories page.
     */
    public function categories()
    {
        return view('pages.publication.categories');
    }

    /**
     * Display trending publications.
     */
    public function trending()
    {
        return view('pages.publication.trending');
    }

    /**
     * Display user's library.
     */
    public function library()
    {
        return view('pages.publication.library');
    }

    /**
     * Display the specified publication.
     */
    public function show($id)
    {
        return view('pages.publication.show', compact('id'));
    }
}

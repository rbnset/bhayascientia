<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/manuscripts/{version}', function (\App\Models\PublicationVersion $version) {

    abort_unless(auth()->check(), 403);

    $path = $version->pdf_file_path;

    abort_unless(Storage::disk('public')->exists($path), 404);

    return response()->file(
        Storage::disk('public')->path($path),
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]
    );
})->name('manuscripts.view');

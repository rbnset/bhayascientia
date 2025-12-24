<?php

namespace App\Http\Controllers;

use App\Models\PublicationVersion;
use Illuminate\Support\Facades\Storage;

class ManuscriptController extends Controller
{
    public function view(PublicationVersion $publicationVersion)
    {
        // Sesuaikan policy/auth kalau perlu (mis. hanya reviewer / admin)
        $path = $publicationVersion->pdf_file_path;

        abort_unless($path, 404);

        // disk public => storage/app/public/...
        abort_unless(Storage::disk('public')->exists($path), 404);

        // Response inline PDF (browser preview)
        return Storage::disk('public')->response(
            $path,
            basename($path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            ]
        );
    }
}

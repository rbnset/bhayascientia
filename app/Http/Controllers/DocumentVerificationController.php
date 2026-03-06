<?php
// app/Http/Controllers/DocumentVerificationController.php

namespace App\Http\Controllers;

use App\Models\DocumentVerification;
use App\Models\PublicationVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DocumentVerificationController extends Controller
{
    public function verify(Request $request, string $code)
    {
        $code = strtoupper(trim($code));

        $cacheKey = 'verify:doc:' . md5($code);
        $result   = Cache::remember($cacheKey, 300, fn() => $this->resolveCode($code));

        if (! $result['valid']) {
            return view('verify.document', [
                'code'         => $code,
                'valid'        => false,
                'version'      => null,
                'pub'          => null,
                'verification' => null,
            ]);
        }

        $verification = DocumentVerification::where('code', $code)->first();

        if ($verification) {
            $verification->recordScan(
                $request->ip(),
                $request->userAgent() ?? 'Unknown'
            );
        }

        return view('verify.document', [
            'code'         => $code,
            'valid'        => true,
            'version'      => $result['version'],
            'pub'          => $result['publication'],
            'verification' => $verification,
        ]);
    }

    private function resolveCode(string $code): array
    {
        if (! preg_match('/^DBK-(\d+)-V(\d+)-([A-F0-9]{6})$/', $code, $m)) {
            return ['valid' => false];
        }

        [, $pubId, $versionNumber, $inputHash] = $m;

        $version = PublicationVersion::query()
            ->with([
                'publication:id,title,status,published_at',
                'publication.authors', // load relasi authors
            ])
            ->where('version_number', (int) $versionNumber)
            ->whereHas('publication', fn($q) => $q->where('id', (int) $pubId))
            ->select(['id', 'publication_id', 'version_number', 'created_at', 'pdf_file_path'])
            ->first();

        if (! $version) {
            return ['valid' => false];
        }

        $expectedHash = strtoupper(substr(
            hash('sha256', $version->publication->id . '-' . $version->id . '-' . config('app.key')),
            0,
            6
        ));

        if ($inputHash !== $expectedHash) {
            return ['valid' => false];
        }

        DocumentVerification::firstOrCreate(
            ['code' => $code],
            ['publication_version_id' => $version->id]
        );

        return [
            'valid'       => true,
            'version'     => $version,
            'publication' => $version->publication,
        ];
    }
}

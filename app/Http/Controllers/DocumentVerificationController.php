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
        $code   = strtoupper(trim($code));

        // Cache hasil verifikasi 5 menit untuk performa
        $cacheKey = 'verify:' . md5($code);
        $result   = Cache::remember($cacheKey, 300, fn() => $this->resolveCode($code));

        // Catat scan (di luar cache agar selalu tercatat)
        if ($result['valid'] && isset($result['verification'])) {
            $result['verification']->recordScan(
                $request->ip(),
                $request->userAgent() ?? 'Unknown'
            );
        }

        return view('verify.document', [
            'code'    => $code,
            'valid'   => $result['valid'],
            'version' => $result['version'] ?? null,
            'pub'     => $result['publication'] ?? null,
            'verification' => $result['verification'] ?? null,
        ]);
    }

    private function resolveCode(string $code): array
    {
        if (! preg_match('/^DBK-(\d+)-V(\d+)-([A-F0-9]{6})$/', $code, $m)) {
            return ['valid' => false];
        }

        [, $pubId, $versionNumber, $inputHash] = $m;

        // Query optimal: single query dengan eager loading
        $version = PublicationVersion::query()
            ->with(['publication:id,title,author,status,created_at'])
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

        // Upsert record verifikasi
        $verification = DocumentVerification::firstOrCreate(
            ['code' => $code],
            ['publication_version_id' => $version->id]
        );

        return [
            'valid'        => true,
            'version'      => $version,
            'publication'  => $version->publication,
            'verification' => $verification,
        ];
    }
}

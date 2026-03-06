<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\PublicationVersion;
use Illuminate\Http\Request;

class DocumentVerificationController extends Controller
{
    /**
     * Format kode: DBK-{pub_id}-V{version}-{hash6}
     * Contoh    : DBK-0029-V2-A3F8C1
     */
    public function verify(Request $request, string $code)
    {
        $code    = strtoupper(trim($code));
        $result  = $this->resolveCode($code);

        return view('verify.document', [
            'code'    => $code,
            'valid'   => $result['valid'],
            'version' => $result['version'] ?? null,
            'pub'     => $result['publication'] ?? null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Resolve & validasi kode
    // ─────────────────────────────────────────────────────────────
    private function resolveCode(string $code): array
    {
        // Validasi format: DBK-XXXX-VX-XXXXXX
        if (! preg_match('/^DBK-(\d+)-V(\d+)-([A-F0-9]{6})$/', $code, $m)) {
            return ['valid' => false];
        }

        [, $pubId, $versionNumber, $inputHash] = $m;

        $version = PublicationVersion::query()
            ->with('publication')
            ->where('version_number', (int) $versionNumber)
            ->whereHas('publication', fn($q) => $q->where('id', (int) $pubId))
            ->first();

        if (! $version) {
            return ['valid' => false];
        }

        // Re-generate hash dengan cara yang sama seperti PdfStamper
        $expectedHash = strtoupper(substr(
            hash('sha256', $version->publication->id . '-' . $version->id . '-' . config('app.key')),
            0,
            6
        ));

        if ($inputHash !== $expectedHash) {
            return ['valid' => false];
        }

        return [
            'valid'       => true,
            'version'     => $version,
            'publication' => $version->publication,
        ];
    }
}

<?php
// app/Models/DocumentVerification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'publication_version_id',
        'ip_address',
        'user_agent',
        'metadata',
        'scan_count',
        'last_scanned_at',
    ];

    protected $casts = [
        'metadata'        => 'array',
        'scan_count'      => 'integer',
        'last_scanned_at' => 'datetime',
    ];

    public function publicationVersion(): BelongsTo
    {
        return $this->belongsTo(PublicationVersion::class);
    }

    public function publication()
    {
        return $this->publicationVersion->publication ?? null;
    }

    /**
     * Catat setiap kali dokumen di-scan/verifikasi
     */
    public function recordScan(string $ip, string $userAgent): void
    {
        $this->increment('scan_count');
        $this->update([
            'ip_address'      => $ip,
            'user_agent'      => $userAgent,
            'last_scanned_at' => now(),
        ]);
    }
}

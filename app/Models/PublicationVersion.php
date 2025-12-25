<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicationVersion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'publication_id',
        'pdf_file_path',
        'version_number',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    protected $appends = ['display_label'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // =====================
    // PUBLICATION
    // =====================
    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }

    // =====================
    // REVIEWS
    // =====================
    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class, 'publication_version_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Label ramah UI untuk Filament Select
     * Contoh: "Version 2 · 12 Dec 2025"
     */
    public function getDisplayLabelAttribute(): string
    {
        return sprintf(
            '%s | v%d | %s',
            $this->publication?->title ?? 'Unknown Publication',
            $this->version_number,
            $this->created_at->translatedFormat('d M Y')
        );
    }
}

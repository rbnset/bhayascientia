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
        return $this->hasMany(Review::class);
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
        $date = $this->submitted_at
            ? $this->submitted_at->format('d M Y')
            : $this->created_at->format('d M Y');

        return "Version {$this->version_number} · {$date}";
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PublicationTypeContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_type_id',
        'title',
        'description',
        'image_path',
    ];

    /**
     * Accessor untuk mendapatkan URL gambar content
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        $cleanPath = $this->image_path;
        if (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, 7);
        }

        if (Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/' . $cleanPath);
        }

        return null;
    }

    /**
     * Relasi belongsTo ke PublicationType
     */
    public function publicationType(): BelongsTo
    {
        return $this->belongsTo(PublicationType::class);
    }
}

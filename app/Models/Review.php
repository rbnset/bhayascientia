<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'publication_version_id',
        'publication_id',
        'reviewer_id',
        'decision',
        'overall_comment',
        'revision_deadline',
    ];

    protected $casts = [
        'revision_deadline' => 'datetime',
    ];

    public function publicationVersion(): BelongsTo
    {
        return $this->belongsTo(PublicationVersion::class);
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ReviewNote::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ReviewAttachment::class);
    }

    public function pdfAnnotations(): HasMany
    {
        return $this->hasMany(PdfAnnotation::class);
    }

    public function scopeAccepted($query)
    {
        return $query->where('decision', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('decision', 'rejected');
    }

    public function scopeRevisionRequired($query)
    {
        return $query->where('decision', 'revision_required');
    }
}

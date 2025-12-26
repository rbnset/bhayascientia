<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'publication_version_id',
        'reviewer_id',
        'decision',
        'overall_comment',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // =====================
    // PUBLICATION VERSION
    // =====================
    public function publicationVersion()
    {
        return $this->belongsTo(PublicationVersion::class);
    }

    // =====================
    // REVIEWER (USER)
    // =====================
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // =====================
    // REVIEW NOTES
    // =====================
    public function notes(): HasMany
    {
        return $this->hasMany(ReviewNote::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ReviewAttachment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

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

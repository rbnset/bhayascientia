<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadLog extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'publication_id',
        'user_id',
        'downloaded_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    /**
     * Log belongs to a Publication.
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    /**
     * Log belongs to a User (nullable).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

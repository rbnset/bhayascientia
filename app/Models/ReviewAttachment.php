<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewAttachment extends Model
{
    protected $fillable = [
        'review_id',
        'file_path',
        'original_name',
    ];

    /*
    |------------------------------------------------------------------
    | Relationships
    |------------------------------------------------------------------
    */

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}

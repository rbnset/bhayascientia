<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewNote extends Model
{
    protected $fillable = [
        'review_id',
        'section',
        'note',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}

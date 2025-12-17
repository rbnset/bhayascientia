<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Author extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'affiliation',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function publications()
    {
        return $this->belongsToMany(Publication::class)
            ->withPivot('order', 'is_corresponding');
    }
}

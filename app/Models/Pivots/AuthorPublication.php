<?php

namespace App\Models\Pivots;

use App\Models\Author;
use App\Models\Publication;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AuthorPublication extends Pivot
{
    protected $table = 'author_publication';

    protected $primaryKey = 'id';

    public $incrementing = true; // sesuai docs custom pivot + auto-increment [web:248]

    protected $fillable = [
        'publication_id',
        'author_id',
        'order',
        'is_corresponding',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_corresponding' => 'boolean',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}

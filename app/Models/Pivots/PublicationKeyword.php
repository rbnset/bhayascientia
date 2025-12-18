<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PublicationKeyword extends Pivot
{
    /**
     * Nama tabel pivot
     */
    protected $table = 'publication_keyword';

    /**
     * Karena pakai composite primary key
     */
    public $incrementing = false;

    /**
     * Tidak pakai timestamps (sesuai migrasi kamu)
     */
    public $timestamps = false;

    /**
     * Mass assignable (future-proof)
     */
    protected $fillable = [
        'publication_id',
        'keyword_id',
    ];
}

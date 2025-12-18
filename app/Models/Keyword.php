<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $fillable = ['name', 'slug'];


    // =====================
    // PUBLICATIONS
    // =====================
    public function publications()
    {
        return $this->belongsToMany(
            Publication::class,
            'publication_keyword'
        );
    }
}

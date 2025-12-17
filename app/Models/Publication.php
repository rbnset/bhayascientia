<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'publication_type_id',
        'title',
        'abstract',
        'category_id',
        'method_id',
        'status'
    ];

    public function authors()
    {
        return $this->belongsToMany(Author::class)
            ->withPivot('order', 'is_corresponding');
    }

    public function versions()
    {
        return $this->hasMany(PublicationVersion::class);
    }
}

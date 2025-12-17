<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicationType extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'requires_review',
        'is_active'
    ];
}

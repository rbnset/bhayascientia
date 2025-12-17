<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicationVersion extends Model
{
    protected $fillable = [
        'publication_id',
        'pdf_file_path',
        'version_number',
        'submitted_at'
    ];
}

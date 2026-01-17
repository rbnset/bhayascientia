<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserReadPublication extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = true;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'last_read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfAnnotation extends Model
{
    protected $table = 'pdf_annotations';

    /**
     * Semua type yang valid.
     * highlight    : stabilo teks
     * underline    : garis bawah teks
     * strikethrough: garis tengah teks
     * freehand     : pen bebas
     * comment      : highlight + catatan
     * sticky       : sticky note
     * shape        : kotak/lingkaran/panah/garis
     * text         : teks bebas di canvas
     */
    public const VALID_TYPES = [
        'highlight',
        'underline',
        'strikethrough',
        'freehand',
        'comment',
        'sticky',
        'shape',
        'text',
    ];

    public const VALID_COLORS = [
        'yellow',
        'green',
        'red',
        'blue',
        'orange',
        'black',
        'white',
        'pink',
        'purple',
        'cyan',
    ];

    public const VALID_SHAPE_TYPES = [
        'rect',
        'ellipse',
        'arrow',
        'line',
    ];

    protected $fillable = [
        'user_id',
        'publication_id',
        'page',
        'type',
        'color',
        'rect_x',
        'rect_y',
        'rect_w',
        'rect_h',
        'selected_text',
        'comment',
        'path_points',
        'shape_type',
        'stroke_width',
        'fill_opacity',
    ];

    protected $casts = [
        'path_points'  => 'array',
        'rect_x'       => 'float',
        'rect_y'       => 'float',
        'rect_w'       => 'float',
        'rect_h'       => 'float',
        'stroke_width' => 'float',
        'fill_opacity' => 'float',
        'page'         => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeForPage($query, int $page)
    {
        return $query->where('page', $page);
    }

    public function scopeForPublication($query, int $publicationId)
    {
        return $query->where('publication_id', $publicationId);
    }
}

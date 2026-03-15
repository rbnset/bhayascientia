<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfAnnotation extends Model
{
    protected $table = 'pdf_annotations';

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
        'review_id',
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

    /**
     * Jika annotasi dibuat dalam konteks review, terhubung ke review ini.
     * Null = annotasi pembaca biasa (bukan reviewer).
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
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

    /**
     * Scope: hanya annotasi reviewer (bukan pembaca biasa).
     */
    public function scopeForReview($query, int $reviewId)
    {
        return $query->where('review_id', $reviewId);
    }

    /**
     * Scope: hanya annotasi pembaca biasa (bukan reviewer).
     */
    public function scopePublicOnly($query)
    {
        return $query->whereNull('review_id');
    }
}

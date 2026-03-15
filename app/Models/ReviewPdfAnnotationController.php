<?php

namespace App\Http\Controllers;

use App\Models\PdfAnnotation;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk anotasi PDF dalam konteks review.
 *
 * Perbedaan dengan PdfAnnotationController:
 * - Semua anotasi dikaitkan ke review_id SEKALIGUS publication_id
 * - Reviewer hanya bisa akses anotasi miliknya sendiri pada review tersebut
 * - Author/Admin bisa melihat semua anotasi review (GET endpoint khusus)
 *
 * Routes (di web.php, prefix /api/review-annotations/{reviewId}):
 *   GET    /         -> index
 *   POST   /         -> store
 *   PUT    /{id}     -> update
 *   DELETE /page/{p} -> destroyPage
 *   DELETE /{id}     -> destroy
 *
 * Route tambahan untuk Author melihat anotasi reviewer:
 *   GET /api/review-annotations/{reviewId}/readonly -> indexReadonly
 */
class ReviewPdfAnnotationController extends Controller
{
    /**
     * Ambil semua anotasi reviewer sendiri pada review ini.
     */
    public function index(int $reviewId): JsonResponse
    {
        $review = Review::findOrFail($reviewId);

        // Pastikan user adalah reviewer review ini atau admin/editor
        $this->authorizeReviewAccess($review);

        $annotations = PdfAnnotation::where('review_id', $reviewId)
            ->where('user_id', Auth::id())
            ->orderBy('page')
            ->orderBy('created_at')
            ->get()
            ->map(fn($a) => $this->serialize($a));

        return response()->json(['data' => $annotations]);
    }

    /**
     * Endpoint READ-ONLY untuk author/admin melihat anotasi reviewer.
     * Mengembalikan semua anotasi dari semua reviewer pada review ini.
     */
    public function indexReadonly(int $reviewId): JsonResponse
    {
        $review = Review::with('reviewer')->findOrFail($reviewId);

        // Pastikan user punya akses: reviewer itu sendiri, atau memiliki
        // publication yang di-review, atau admin/editor
        $this->authorizeReadonlyAccess($review);

        $annotations = PdfAnnotation::where('review_id', $reviewId)
            ->orderBy('page')
            ->orderBy('created_at')
            ->get()
            ->map(fn($a) => $this->serialize($a));

        return response()->json([
            'data'     => $annotations,
            'reviewer' => [
                'id'   => $review->reviewer?->id,
                'name' => $review->reviewer?->name,
            ],
        ]);
    }

    /**
     * Simpan anotasi baru.
     */
    public function store(Request $request, int $reviewId): JsonResponse
    {
        $review = Review::findOrFail($reviewId);
        $this->authorizeReviewAccess($review);

        $validated = $request->validate([
            'page'          => 'required|integer|min:1',
            'type'          => 'required|in:' . implode(',', PdfAnnotation::VALID_TYPES),
            'color'         => 'required|in:' . implode(',', PdfAnnotation::VALID_COLORS),
            'rect_x'        => 'nullable|numeric',
            'rect_y'        => 'nullable|numeric',
            'rect_w'        => 'nullable|numeric',
            'rect_h'        => 'nullable|numeric',
            'selected_text' => 'nullable|string|max:2000',
            'comment'       => 'nullable|string|max:2000',
            'path_points'   => 'nullable|array',
            'path_points.*' => 'array',
            'shape_type'    => 'nullable|in:' . implode(',', PdfAnnotation::VALID_SHAPE_TYPES),
            'stroke_width'  => 'nullable|numeric|min:0.5|max:50',
            'fill_opacity'  => 'nullable|numeric|min:0|max:1',
        ]);

        $annotation = PdfAnnotation::create([
            'user_id'        => Auth::id(),
            'publication_id' => $review->publicationVersion->publication_id,
            'review_id'      => $reviewId,
            ...$validated,
        ]);

        return response()->json(['data' => $this->serialize($annotation)], 201);
    }

    /**
     * Update anotasi (posisi sticky, warna, komentar, dll).
     */
    public function update(Request $request, int $reviewId, int $id): JsonResponse
    {
        $annotation = PdfAnnotation::where('id', $id)
            ->where('review_id', $reviewId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'comment'      => 'nullable|string|max:2000',
            'color'        => 'nullable|in:' . implode(',', PdfAnnotation::VALID_COLORS),
            'stroke_width' => 'nullable|numeric|min:0.5|max:50',
            'fill_opacity' => 'nullable|numeric|min:0|max:1',
            'rect_x'       => 'nullable|numeric',
            'rect_y'       => 'nullable|numeric',
            'rect_w'       => 'nullable|numeric',
            'rect_h'       => 'nullable|numeric',
        ]);

        $annotation->update($validated);

        return response()->json(['data' => $this->serialize($annotation)]);
    }

    /**
     * Hapus satu anotasi.
     */
    public function destroy(int $reviewId, int $id): JsonResponse
    {
        $annotation = PdfAnnotation::where('id', $id)
            ->where('review_id', $reviewId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $annotation->delete();

        return response()->json(['message' => 'Deleted']);
    }

    /**
     * Hapus semua anotasi di satu halaman pada review ini.
     */
    public function destroyPage(int $reviewId, int $page): JsonResponse
    {
        PdfAnnotation::where('review_id', $reviewId)
            ->where('user_id', Auth::id())
            ->where('page', $page)
            ->delete();

        return response()->json(['message' => 'Page cleared']);
    }

    // ─────────────────────────────────────────────────────────────────
    // Authorization Helpers
    // ─────────────────────────────────────────────────────────────────

    /**
     * Pastikan user adalah reviewer dari review ini (atau admin).
     */
    private function authorizeReviewAccess(Review $review): void
    {
        $user = Auth::user();

        // Admin & editor bisa akses semua
        if ($user->hasAnyRole(['super_admin', 'admin', 'editor'])) {
            return;
        }

        // Reviewer hanya bisa akses review miliknya
        abort_unless(
            $review->reviewer_id === $user->id,
            403,
            'Anda tidak memiliki akses ke review ini.'
        );
    }

    /**
     * Pastikan user punya akses readonly:
     * - Reviewer itu sendiri
     * - Author dari publikasi yang di-review
     * - Admin/editor
     */
    private function authorizeReadonlyAccess(Review $review): void
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['super_admin', 'admin', 'editor'])) {
            return;
        }

        if ($review->reviewer_id === $user->id) {
            return;
        }

        // Author: cek apakah user adalah author di publikasi terkait
        $publicationId = $review->publicationVersion?->publication_id;
        if ($publicationId) {
            $isAuthor = \App\Models\Author::where('user_id', $user->id)
                ->whereHas('publications', fn($q) => $q->where('id', $publicationId))
                ->exists();

            if ($isAuthor) return;
        }

        abort(403, 'Anda tidak memiliki akses ke anotasi ini.');
    }

    // ─────────────────────────────────────────────────────────────────
    // Serializer
    // ─────────────────────────────────────────────────────────────────

    private function serialize(PdfAnnotation $a): array
    {
        return [
            'id'            => $a->id,
            'page'          => $a->page,
            'type'          => $a->type,
            'color'         => $a->color,
            'rect'          => ($a->rect_x !== null) ? [
                'x' => $a->rect_x,
                'y' => $a->rect_y,
                'w' => $a->rect_w,
                'h' => $a->rect_h,
            ] : null,
            'selected_text' => $a->selected_text,
            'comment'       => $a->comment,
            'path_points'   => $a->path_points,
            'shape_type'    => $a->shape_type,
            'stroke_width'  => $a->stroke_width,
            'fill_opacity'  => $a->fill_opacity,
            'created_at'    => $a->created_at?->toISOString(),
        ];
    }
}

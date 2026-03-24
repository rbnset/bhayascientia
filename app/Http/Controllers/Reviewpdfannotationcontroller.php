<?php

namespace App\Http\Controllers;

use App\Models\PdfAnnotation;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * FIX v2 — 3 perbaikan dari versi sebelumnya:
 *
 * FIX 1 — serialize() tidak mengembalikan arrow_x1/y1/x2/y2
 *   Shape arrow & line tidak muncul setelah reload karena JS (rSH) butuh
 *   a.arrow_x1 dst. Sekarang serialize() mengisi dari kolom langsung,
 *   atau fallback ke path_points[[x1,y1],[x2,y2]] jika kolom belum ada.
 *
 * FIX 2 — store() fatal error jika publicationVersion null
 *   $review->publicationVersion->publication_id melempar error jika relasi
 *   tidak ter-load. Sekarang pakai null-safe ?-> dan fallback ke null.
 *
 * FIX 3 — store() tidak menyimpan arrow_x1/y1/x2/y2
 *   JS mengirim arrow coords tapi controller tidak menerimanya.
 *   Sekarang divalidasi dan disimpan jika kolom sudah ada di tabel.
 *   Jika belum di-migrate, data tetap aman via path_points.
 */
class ReviewPdfAnnotationController extends Controller
{
    /**
     * Ambil semua anotasi reviewer sendiri pada review ini.
     */
    public function index(int $reviewId): JsonResponse
    {
        $review = Review::findOrFail($reviewId);

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
     */
    public function indexReadonly(int $reviewId): JsonResponse
    {
        $review = Review::with('reviewer')->findOrFail($reviewId);

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
            // FIX 3: terima arrow coords dari JS
            'arrow_x1'      => 'nullable|numeric',
            'arrow_y1'      => 'nullable|numeric',
            'arrow_x2'      => 'nullable|numeric',
            'arrow_y2'      => 'nullable|numeric',
        ]);

        // FIX 2: null-safe — tidak melempar error jika relasi null
        $publicationId = $review->publicationVersion?->publication_id
            ?? $review->publication_version?->publication_id
            ?? null;

        // FIX 3: hanya isi kolom arrow jika sudah ada di tabel
        // (backward-compatible — tidak error jika migration belum jalan)
        $arrowData = [];
        if (\Illuminate\Support\Facades\Schema::hasColumn('pdf_annotations', 'arrow_x1')) {
            $arrowData = [
                'arrow_x1' => $validated['arrow_x1'] ?? null,
                'arrow_y1' => $validated['arrow_y1'] ?? null,
                'arrow_x2' => $validated['arrow_x2'] ?? null,
                'arrow_y2' => $validated['arrow_y2'] ?? null,
            ];
        }

        $annotation = PdfAnnotation::create([
            'user_id'        => Auth::id(),
            'publication_id' => $publicationId,
            'review_id'      => $reviewId,
            ...$validated,
            ...$arrowData,
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

        return response()->json(['data' => $this->serialize($annotation->fresh())]);
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

    private function authorizeReviewAccess(Review $review): void
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['super_admin', 'admin', 'editor'])) {
            return;
        }

        abort_unless(
            $review->reviewer_id === $user->id,
            403,
            'Anda tidak memiliki akses ke review ini.'
        );
    }

    private function authorizeReadonlyAccess(Review $review): void
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['super_admin', 'admin', 'editor'])) {
            return;
        }

        if ($review->reviewer_id === $user->id) {
            return;
        }

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

    /**
     * FIX 1: kembalikan arrow_x1/y1/x2/y2 agar JS (rSH) bisa render
     * shape arrow & line setelah reload. Fallback ke path_points jika
     * kolom arrow belum ada di tabel.
     */
    private function serialize(PdfAnnotation $a): array
    {
        // Ambil arrow coords dari kolom langsung jika ada
        $arrowX1 = $a->arrow_x1 ?? null;
        $arrowY1 = $a->arrow_y1 ?? null;
        $arrowX2 = $a->arrow_x2 ?? null;
        $arrowY2 = $a->arrow_y2 ?? null;

        // Fallback: ekstrak dari path_points jika kolom kosong
        if (
            $arrowX1 === null &&
            in_array($a->shape_type, ['arrow', 'line'], true) &&
            is_array($a->path_points) &&
            count($a->path_points) >= 2
        ) {
            $arrowX1 = (float) ($a->path_points[0][0] ?? 0);
            $arrowY1 = (float) ($a->path_points[0][1] ?? 0);
            $arrowX2 = (float) ($a->path_points[1][0] ?? 0);
            $arrowY2 = (float) ($a->path_points[1][1] ?? 0);
        }

        return [
            'id'            => $a->id,
            'page'          => $a->page,
            'type'          => $a->type,
            'color'         => $a->color,
            // Objek rect untuk kemudahan JS (a.rect.x)
            'rect'          => ($a->rect_x !== null) ? [
                'x' => $a->rect_x,
                'y' => $a->rect_y,
                'w' => $a->rect_w,
                'h' => $a->rect_h,
            ] : null,
            // Flat juga untuk fallback JS
            'rect_x'        => $a->rect_x,
            'rect_y'        => $a->rect_y,
            'rect_w'        => $a->rect_w,
            'rect_h'        => $a->rect_h,
            'selected_text' => $a->selected_text,
            'comment'       => $a->comment,
            'path_points'   => $a->path_points,
            'shape_type'    => $a->shape_type,
            'stroke_width'  => $a->stroke_width,
            'fill_opacity'  => $a->fill_opacity,
            // FIX 1: wajib ada agar rSH() di JS bisa render arrow/line
            'arrow_x1'      => $arrowX1,
            'arrow_y1'      => $arrowY1,
            'arrow_x2'      => $arrowX2,
            'arrow_y2'      => $arrowY2,
            'created_at'    => $a->created_at?->toISOString(),
        ];
    }
}

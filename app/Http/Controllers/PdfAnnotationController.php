<?php

namespace App\Http\Controllers;

use App\Models\PdfAnnotation;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PdfAnnotationController extends Controller
{
    public function index(string $slug): JsonResponse
    {
        $publication = Publication::where('slug', $slug)->firstOrFail();
        $annotations = PdfAnnotation::where('user_id', Auth::id())
            ->where('publication_id', $publication->id)
            ->orderBy('page')->orderBy('created_at')
            ->get()->map(fn($a) => $this->serialize($a));
        return response()->json(['data' => $annotations]);
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $publication = Publication::where('slug', $slug)->firstOrFail();
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
            'publication_id' => $publication->id,
            ...$validated,
        ]);
        return response()->json(['data' => $this->serialize($annotation)], 201);
    }

    /**
     * PUT /api/annotations/{slug}/{id}
     * Digunakan untuk update comment/color DAN update posisi (rect_x, rect_y) sticky note setelah drag.
     */
    public function update(Request $request, string $slug, int $id): JsonResponse
    {
        $annotation = PdfAnnotation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'comment'      => 'nullable|string|max:2000',
            'color'        => 'nullable|in:' . implode(',', PdfAnnotation::VALID_COLORS),
            'stroke_width' => 'nullable|numeric|min:0.5|max:50',
            'fill_opacity' => 'nullable|numeric|min:0|max:1',
            /* Posisi baru untuk sticky drag */
            'rect_x'       => 'nullable|numeric',
            'rect_y'       => 'nullable|numeric',
            'rect_w'       => 'nullable|numeric',
            'rect_h'       => 'nullable|numeric',
        ]);

        $annotation->update($validated);
        return response()->json(['data' => $this->serialize($annotation)]);
    }

    public function destroy(string $slug, int $id): JsonResponse
    {
        $annotation = PdfAnnotation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        $annotation->delete();
        return response()->json(['message' => 'Deleted']);
    }

    /**
     * DELETE /api/annotations/{slug}/page/{page}
     * PASTIKAN route ini didaftarkan SEBELUM route {id} di routes/api.php:
     *
     *   Route::delete('annotations/{slug}/page/{page}', [PdfAnnotationController::class, 'destroyPage']);
     *   Route::delete('annotations/{slug}/{id}',        [PdfAnnotationController::class, 'destroy']);
     */
    public function destroyPage(string $slug, int $page): JsonResponse
    {
        $publication = Publication::where('slug', $slug)->firstOrFail();
        PdfAnnotation::where('user_id', Auth::id())
            ->where('publication_id', $publication->id)
            ->where('page', $page)
            ->delete();
        return response()->json(['message' => 'Page cleared']);
    }

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

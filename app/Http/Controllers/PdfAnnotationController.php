<?php

namespace App\Http\Controllers;

use App\Models\PdfAnnotation;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PdfAnnotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * GET /api/annotations/{slug}
     * Fetch all annotations for a publication (current user only).
     */
    public function index(string $slug): JsonResponse
    {
        $publication = Publication::where('slug', $slug)->firstOrFail();

        $annotations = PdfAnnotation::where('user_id', Auth::id())
            ->where('publication_id', $publication->id)
            ->orderBy('page')
            ->orderBy('created_at')
            ->get()
            ->map(fn($a) => $this->serialize($a));

        return response()->json(['data' => $annotations]);
    }

    /**
     * POST /api/annotations/{slug}
     * Create a new annotation.
     */
    public function store(Request $request, string $slug): JsonResponse
    {
        $publication = Publication::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'page'          => 'required|integer|min:1',
            'type'          => 'required|in:highlight,freehand,comment,sticky,shape',
            'color'         => 'required|in:yellow,green,red,blue,orange,black,white',
            'rect_x'        => 'nullable|numeric',
            'rect_y'        => 'nullable|numeric',
            'rect_w'        => 'nullable|numeric',
            'rect_h'        => 'nullable|numeric',
            'selected_text' => 'nullable|string|max:2000',
            'comment'       => 'nullable|string|max:2000',
            'path_points'   => 'nullable|array',
            'path_points.*' => 'array',
            'shape_type'    => 'nullable|in:arrow,rect,ellipse',
            'stroke_width'  => 'nullable|numeric|min:0.5|max:20',
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
     * Update comment / color of existing annotation.
     */
    public function update(Request $request, string $slug, int $id): JsonResponse
    {
        $annotation = PdfAnnotation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'comment'      => 'nullable|string|max:2000',
            'color'        => 'nullable|in:yellow,green,red,blue,orange,black,white',
            'stroke_width' => 'nullable|numeric|min:0.5|max:20',
            'fill_opacity' => 'nullable|numeric|min:0|max:1',
        ]);

        $annotation->update($validated);

        return response()->json(['data' => $this->serialize($annotation)]);
    }

    /**
     * DELETE /api/annotations/{slug}/{id}
     */
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
     * Clear all annotations on a specific page for current user.
     */
    public function destroyPage(string $slug, int $page): JsonResponse
    {
        $publication = Publication::where('slug', $slug)->firstOrFail();

        PdfAnnotation::where('user_id', Auth::id())
            ->where('publication_id', $publication->id)
            ->where('page', $page)
            ->delete();

        return response()->json(['message' => 'Page annotations cleared']);
    }

    // ── Helpers ───────────────────────────────────────────────────────

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

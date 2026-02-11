<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Models\Category;

class PublicationCategoriesController extends Controller
{
    public function categories()
    {
        $categories = Category::whereHas('publications', function ($query) {
            $query->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        })
            ->withCount(['publications' => function ($query) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            }])
            ->having('publications_count', '>', 0)
            ->orderByDesc('publications_count')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'publications_count' => $category->publications_count,
                    'icon' => $category->icon ?? asset('images/icons/category-default.svg'),
                    'color' => $category->color ?? '#FF6B18',
                ];
            });

        return view('pages.publication.categories', compact('categories'));
    }
}

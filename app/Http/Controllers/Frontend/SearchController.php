<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q', '');
        $results = [
            'posts' => collect(),
            'pages' => collect(),
            'categories' => collect(),
        ];

        if (strlen($q) >= 2) {
            $results['posts'] = Post::published()
                ->with('featuredMedia:id,path')
                ->where(function ($query) use ($q) {
                    $query->where('title', 'LIKE', "%{$q}%")
                        ->orWhere('excerpt', 'LIKE', "%{$q}%")
                        ->orWhere('content', 'LIKE', "%{$q}%");
                })
                ->latest('published_at')
                ->take(20)
                ->get();

            $results['pages'] = Page::published()
                ->where(function ($query) use ($q) {
                    $query->where('title', 'LIKE', "%{$q}%")
                        ->orWhere('content', 'LIKE', "%{$q}%");
                })
                ->take(10)
                ->get();

            $results['categories'] = Category::active()
                ->where('name', 'LIKE', "%{$q}%")
                ->take(10)
                ->get();
        }

        $totalResults = $results['posts']->count()
            + $results['pages']->count()
            + $results['categories']->count();

        return view('frontend.search', compact('q', 'results', 'totalResults'));
    }

    public function search(Request $request)
    {
        $q = $request->get('q', '');

        if (strlen($q) < 2) {
            return response()->json([
                'success' => true,
                'data' => [
                    'posts' => [],
                    'pages' => [],
                    'categories' => [],
                ],
            ]);
        }

        $posts = Post::published()
            ->with('featuredMedia:id,path')
            ->where(function ($query) use ($q) {
                $query->where('title', 'LIKE', "%{$q}%")
                    ->orWhere('excerpt', 'LIKE', "%{$q}%")
                    ->orWhere('content', 'LIKE', "%{$q}%");
            })
            ->latest('published_at')
            ->take(20)
            ->get(['id', 'title', 'slug', 'excerpt', 'featured_media_id', 'published_at']);

        $pages = Page::published()
            ->where(function ($query) use ($q) {
                $query->where('title', 'LIKE', "%{$q}%")
                    ->orWhere('content', 'LIKE', "%{$q}%");
            })
            ->take(10)
            ->get(['id', 'title', 'slug']);

        $categories = Category::active()
            ->where('name', 'LIKE', "%{$q}%")
            ->take(10)
            ->get(['id', 'name', 'slug']);

        return response()->json([
            'success' => true,
            'data' => [
                'posts' => $posts,
                'pages' => $pages,
                'categories' => $categories,
            ],
        ]);
    }
}

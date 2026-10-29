<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = Category::active()->where('slug', $slug)->firstOrFail();

        $postsPerPage = (int) \App\Models\Setting::get('posts_per_page', 10);

        $posts = Post::published()
            ->where('category_id', $category->id)
            ->with(['featuredMedia', 'author:id,name'])
            ->latest('published_at')
            ->paginate($postsPerPage)
            ->withQueryString();

        return view('frontend.categories.show', compact('category', 'posts'));
    }
}

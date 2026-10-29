<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        $postsPerPage = (int) Setting::get('posts_per_page', 10);

        $slideshowPosts = Post::published()
            ->with(['category:id,name,slug', 'featuredMedia'])
            ->latest('published_at')
            ->take(5)
            ->get();

        $latestPosts = Post::published()
            ->whereNotIn('id', $slideshowPosts->pluck('id'))
            ->with(['category:id,name,slug', 'featuredMedia'])
            ->latest('published_at')
            ->take($postsPerPage)
            ->get();

        $popularPosts = Post::published()
            ->with(['category:id,name,slug', 'featuredMedia'])
            ->selectRaw('*, views + (rating_average * rating_count) as popularity_score')
            ->orderByDesc('popularity_score')
            ->take(5)
            ->get();

        $aboutPage = Page::published()->where('slug', 'tentang-lawnu')->first();
        $menus = Menu::active()->topLevel()->get();

        return view('frontend.home', compact(
            'slideshowPosts',
            'latestPosts',
            'popularPosts',
            'aboutPage',
            'menus'
        ));
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    public function index(Request $request)
    {
        $postsPerPage = (int) \App\Models\Setting::get('posts_per_page', 10);

        $posts = Post::published()
            ->with(['category:id,name,slug', 'featuredMedia', 'author:id,name'])
            ->when($request->tag, fn ($q, $tag) => $q->whereHas('tags', fn ($q) => $q->where('tags.slug', $tag)))
            ->latest('published_at')
            ->paginate($postsPerPage)
            ->withQueryString();

        return view('frontend.posts.index', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = Post::published()
            ->where('slug', $slug)
            ->with(['category:id,name,slug', 'tags', 'featuredMedia', 'author:id,name', 'media.media'])
            ->firstOrFail();

        $this->postService->incrementViews($post);

        $relatedPosts = $this->postService->getRelated($post, 5);
        $previousPost = $this->postService->getPrevious($post);
        $nextPost = $this->postService->getNext($post);

        return view('frontend.posts.show', compact('post', 'relatedPosts', 'previousPost', 'nextPost'));
    }
}

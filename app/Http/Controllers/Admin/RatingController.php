<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::published()
            ->withCount('ratings')
            ->whereHas('ratings')
            ->orderByDesc('ratings_count')
            ->paginate(20)
            ->withQueryString();

        // Get average rating per post
        $postIds = $posts->pluck('id');
        $avgRatings = Rating::whereIn('post_id', $postIds)
            ->selectRaw('post_id, AVG(rating) as avg_rating, COUNT(*) as cnt')
            ->groupBy('post_id')
            ->pluck('avg_rating', 'post_id');

        $totalRatings = Rating::count();
        $avgRating = Rating::avg('rating') ?? 0;

        $topPost = $posts->first();

        return view('admin.ratings.index', [
            'posts' => $posts,
            'avgRatings' => $avgRatings,
            'totalRatings' => $totalRatings,
            'avgRating' => $avgRating,
            'topPost' => $topPost,
        ]);
    }
}

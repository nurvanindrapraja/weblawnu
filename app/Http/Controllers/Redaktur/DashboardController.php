<?php

namespace App\Http\Controllers\Redaktur;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Post;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'submitted' => Post::submitted()->count(),
            'review' => Post::inReview()->count(),
            'approved' => Post::approved()->count(),
            'rejected' => Post::rejected()->count(),
            'published' => Post::published()->count(),
            'total' => Post::count(),
            'galleries_submitted' => Gallery::submitted()->count(),
        ];

        $pendingReview = Post::whereIn('status', ['submitted', 'review'])
            ->with(['author:id,name', 'category:id,name,slug'])
            ->latest('submitted_at')
            ->take(10)
            ->get();

        return view('redaktur.dashboard', [
            'stats' => $stats,
            'pendingPosts' => $pendingReview,
        ]);
    }

    public function statistics()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'posts' => [
                    'total' => Post::count(),
                    'submitted' => Post::submitted()->count(),
                    'review' => Post::inReview()->count(),
                    'approved' => Post::approved()->count(),
                    'rejected' => Post::rejected()->count(),
                    'published' => Post::published()->count(),
                ],
            ],
        ]);
    }
}

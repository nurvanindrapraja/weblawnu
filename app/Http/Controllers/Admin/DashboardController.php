<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Gallery;
use App\Models\Post;
use App\Models\Rating;
use App\Models\User;
use App\Services\ActivityLogService;

class DashboardController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLogService
    ) {}

    public function index()
    {
        $stats = [
            'posts' => [
                'total' => Post::count(),
                'draft' => Post::draft()->count(),
                'submitted' => Post::submitted()->count(),
                'review' => Post::inReview()->count(),
                'approved' => Post::approved()->count(),
                'published' => Post::published()->count(),
                'rejected' => Post::rejected()->count(),
            ],
            'users' => User::count(),
            'contact_messages' => ContactMessage::count(),
            'new_messages' => ContactMessage::new()->count(),
            'ratings' => Rating::count(),
            'galleries' => [
                'submitted' => Gallery::submitted()->count(),
            ],
        ];

        $recentPosts = Post::with(['author:id,name', 'category:id,name,slug'])
            ->latest('created_at')
            ->take(5)
            ->get();

        $recentMessages = ContactMessage::latest()->take(5)->get();

        $recentLogs = $this->activityLogService->getRecent(10);

        return view('admin.dashboard', compact('stats', 'recentPosts', 'recentMessages', 'recentLogs'));
    }

    public function statistics()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'posts' => [
                    'total' => Post::count(),
                    'draft' => Post::draft()->count(),
                    'review' => Post::inReview()->count() + Post::submitted()->count(),
                    'published' => Post::published()->count(),
                ],
                'users' => User::count(),
                'contact_messages' => ContactMessage::count(),
                'new_messages' => ContactMessage::new()->count(),
                'ratings' => Rating::count(),
            ],
        ]);
    }
}

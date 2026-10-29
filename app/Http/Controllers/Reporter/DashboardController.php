<?php

namespace App\Http\Controllers\Reporter;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $stats = [
            'total' => $user->posts()->count(),
            'draft' => $user->posts()->draft()->count(),
            'sent' => $user->posts()->where('status', '!=', 'draft')->count(),
            'under_review' => $user->posts()->whereIn('status', ['submitted', 'review'])->count(),
            'submitted' => $user->posts()->submitted()->count(),
            'rejected' => $user->posts()->rejected()->count(),
            'published' => $user->posts()->published()->count(),
        ];

        $recentPosts = $user->posts()
            ->with('category:id,name,slug')
            ->latest('updated_at')
            ->take(5)
            ->get();

        $recentLogs = ActivityLog::where('user_id', $user->id)
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('reporter.dashboard', compact('stats', 'recentPosts', 'recentLogs'));
    }

    public function statistics()
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => [
                'posts' => [
                    'total' => $user->posts()->count(),
                    'draft' => $user->posts()->draft()->count(),
                    'sent' => $user->posts()->where('status', '!=', 'draft')->count(),
                    'under_review' => $user->posts()->whereIn('status', ['submitted', 'review'])->count(),
                    'submitted' => $user->posts()->submitted()->count(),
                    'rejected' => $user->posts()->rejected()->count(),
                    'published' => $user->posts()->published()->count(),
                ],
            ],
        ]);
    }
}

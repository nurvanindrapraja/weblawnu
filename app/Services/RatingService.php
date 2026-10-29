<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingService
{
    public function getStats(Post $post): array
    {
        return [
            'average' => $post->rating_average ?: 0,
            'count' => $post->rating_count,
        ];
    }

    public function submit(Post $post, int $rating, Request $request): array
    {
        $visitorHash = $this->generateVisitorHash($request);
        $sessionHash = $request->session()->getId();

        // Check if already rated
        $existing = Rating::where('post_id', $post->id)
            ->where('visitor_hash', $visitorHash)
            ->first();

        if ($existing) {
            return [
                'success' => false,
                'message' => 'Anda sudah memberikan rating untuk berita ini.',
            ];
        }

        // Create rating
        Rating::create([
            'post_id' => $post->id,
            'rating' => $rating,
            'visitor_hash' => $visitorHash,
            'session_hash' => $sessionHash,
        ]);

        // Update post rating averages
        $stats = Rating::where('post_id', $post->id)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total')
            ->first();

        $post->update([
            'rating_average' => round($stats->avg_rating, 2),
            'rating_count' => $stats->total,
        ]);

        return [
            'success' => true,
            'message' => 'Terima kasih atas penilaian Anda.',
            'data' => [
                'average' => $post->rating_average,
                'count' => $post->rating_count,
            ],
        ];
    }

    private function generateVisitorHash(Request $request): string
    {
        $ip = $request->ip() ?? '';
        $userAgent = $request->userAgent() ?? '';

        return hash('sha256', $ip . '|' . $userAgent);
    }
}

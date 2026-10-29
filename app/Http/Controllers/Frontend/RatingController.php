<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\RatingService;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function __construct(
        private RatingService $ratingService
    ) {}

    public function show(Request $request, Post $post)
    {
        $stats = $this->ratingService->getStats($post);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function store(Request $request, Post $post)
    {
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $result = $this->ratingService->submit($post, $data['rating'], $request);

        return response()->json($result, $result['success'] ? 201 : 400);
    }
}

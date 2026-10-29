<?php

namespace App\Http\Controllers\Redaktur;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;

class PostReviewController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    public function index(Request $request)
    {
        $posts = Post::with(['author:id,name', 'category:id,name,slug'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where('title', 'LIKE', "%{$s}%"))
            ->orderByDesc('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('redaktur.posts.index', compact('posts'));
    }

    public function reviewList()
    {
        $posts = Post::whereIn('status', ['submitted', 'review'])
            ->with(['author:id,name', 'category:id,name,slug'])
            ->latest('submitted_at')
            ->paginate(20);

        return view('redaktur.posts.review', compact('posts'));
    }

    public function review(Post $post)
    {
        if ($post->status === 'submitted') {
            $this->postService->startReview($post, auth()->user());
            $post->refresh();
        }

        // Setelah ditolak/disetujui/dipublish, halaman ini tetap bisa
        // diakses (mis. browser di-refresh) — tampilkan banner status
        // alih-alih 404, dan sembunyikan tombol aksi.
        $reviewable = in_array($post->status, ['submitted', 'review']);

        $post->load(['author', 'category', 'tags', 'featuredMedia', 'media']);

        return view('redaktur.posts.review-detail', compact('post', 'reviewable'));
    }

    /**
     * Public-look preview of a post, for any status.
     */
    public function preview(Post $post)
    {
        $post->load(['author:id,name', 'category:id,name,slug', 'tags', 'featuredMedia']);

        return $this->postService->previewView($post);
    }

    public function startReview(Request $request, Post $post)
    {
        if ($post->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'Berita sudah dalam proses review.',
            ], 400);
        }

        $this->postService->startReview($post, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Review dimulai.',
        ]);
    }

    public function approve(Request $request, Post $post)
    {
        if (! in_array($post->status, ['submitted', 'review'])) {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak dapat disetujui.',
            ], 400);
        }

        $this->postService->approve($post, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil disetujui.',
        ]);
    }

    public function reject(Request $request, Post $post)
    {
        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        if (! in_array($post->status, ['submitted', 'review'])) {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak dapat ditolak.',
            ], 400);
        }

        $this->postService->reject($post, $request->user(), $request->note);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil ditolak.',
        ]);
    }

    public function publish(Request $request, Post $post)
    {
        if ($post->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya berita approved yang dapat dipublikasikan.',
            ], 400);
        }

        $this->postService->publish($post, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil dipublikasikan.',
        ]);
    }

    public function unpublish(Request $request, Post $post)
    {
        if ($post->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak sedang dipublikasikan.',
            ], 400);
        }

        $this->postService->unpublish($post, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil diunpublish.',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Reporter;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    public function index(Request $request)
    {
        $posts = $this->postsQuery(auth()->user(), $request)
            ->paginate(20)
            ->withQueryString();

        return view('reporter.posts.index', compact('posts'));
    }

    /**
     * AJAX endpoint: returns filtered table rows + pagination as HTML fragments.
     */
    public function filter(Request $request)
    {
        $posts = $this->postsQuery(auth()->user(), $request)
            ->paginate(20)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'rows' => view('reporter.posts.partials.rows', compact('posts'))->render(),
            'pagination' => (string) $posts->links(),
            'page' => $posts->currentPage(),
            'total' => $posts->total(),
        ]);
    }

    private function postsQuery(User $user, Request $request)
    {
        return $user->posts()
            ->with(['category:id,name,slug', 'tags'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where('title', 'LIKE', "%{$s}%"))
            ->orderByDesc('updated_at');
    }

    /**
     * Public-look preview of the reporter's own post, for any status.
     */
    public function preview(Request $request, Post $post)
    {
        if ($post->author_id !== $request->user()->id) {
            abort(403, 'Anda tidak dapat melihat berita milik reporter lain.');
        }

        $post->load(['author:id,name', 'category:id,name,slug', 'tags', 'featuredMedia']);

        return $this->postService->previewView($post);
    }

    /**
     * Public-look preview of a new (not yet saved) post from the create form.
     */
    public function previewDraft(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'featured_media_id' => 'nullable|exists:media,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $post = new Post([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['title']),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'category_id' => $data['category_id'],
            'status' => 'draft',
            'views' => 0,
            'rating_average' => 0,
            'rating_count' => 0,
            'published_at' => now(),
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ]);
        $post->author = $request->user();
        $post->category = Category::find($data['category_id']);
        $post->tags = Tag::whereIn('id', $data['tags'] ?? [])->get();
        if (! empty($data['featured_media_id'])) {
            $post->featuredMedia = Media::find($data['featured_media_id']);
        }

        return $this->postService->previewView($post);
    }

    public function create()
    {
        $categories = Category::active()->get();
        $tags = Tag::all();

        return view('reporter.posts.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'featured_media_id' => 'nullable|exists:media,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'media' => 'nullable|array',
            'media.*' => 'exists:media,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $data['status'] = 'draft';

        $post = $this->postService->create($data, $request->user());

        if (! empty($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }

        if (! empty($data['media'])) {
            $post->media()->sync($data['media']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Draft berita berhasil disimpan.',
            'data' => $post,
        ], 201);
    }

    public function show(Post $post)
    {
        $this->authorizePost($post);

        $post->load(['author', 'category', 'tags', 'featuredMedia', 'media']);

        return view('reporter.posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $this->authorizePost($post);

        // Consistent with update(): only draft or rejected posts are editable.
        if (! in_array($post->status, ['draft', 'rejected'])) {
            abort(403, 'Berita ini tidak dapat diedit.');
        }

        $categories = Category::active()->get();
        $tags = Tag::all();

        return view('reporter.posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorizePost($post);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'featured_media_id' => 'nullable|exists:media/id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags/id',
            'media' => 'nullable|array',
            'media.*' => 'exists:media/id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        // Only allow updating draft or rejected posts
        if (! in_array($post->status, ['draft', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat mengedit berita draft atau yang ditolak.',
            ], 403);
        }

        if ($post->status === 'rejected') {
            $data['status'] = 'draft';
            $data['rejection_note'] = null;
        }

        $this->postService->update($post, $data);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil diupdate.',
            'data' => $post->refresh(),
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        $this->authorizePost($post);

        if ($post->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat menghapus berita draft.',
            ], 403);
        }

        $this->postService->delete($post);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil dihapus.',
        ]);
    }

    public function submit(Request $request, Post $post)
    {
        $this->authorizePost($post);

        if (! in_array($post->status, ['draft', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya berita draft yang dapat disubmit.',
            ], 403);
        }

        $this->postService->submit($post, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil disubmit untuk review.',
        ]);
    }

    private function authorizePost(Post $post): void
    {
        if ($post->author_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak dapat mengakses berita ini.');
        }
    }
}

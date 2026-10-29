<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
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
        $posts = $this->postsQuery($request)
            ->paginate(20)
            ->withQueryString();

        $categories = Category::active()->get();

        return view('admin.posts.index', compact('posts', 'categories'));
    }

    /**
     * AJAX endpoint: returns filtered table rows + pagination as HTML fragments.
     */
    public function filter(Request $request)
    {
        $posts = $this->postsQuery($request)
            ->paginate(20)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'rows' => view('admin.posts.partials.rows', compact('posts'))->render(),
            'pagination' => (string) $posts->links(),
            'page' => $posts->currentPage(),
            'total' => $posts->total(),
        ]);
    }

    /**
     * Kolom yang boleh di-sort via query param `sort` (whitelist).
     * Nilai: [kolom SQL, arah default].
     *
     * @return array<string, array{0: string, 1: string}>
     */
    private function sortableColumns(): array
    {
        return [
            'title' => ['posts.title', 'asc'],
            'category' => ['categories.name', 'asc'],
            'author' => ['users.name', 'asc'],
            'published' => ['posts.published_at', 'desc'],
            'views' => ['posts.views', 'desc'],
        ];
    }

    private function postsQuery(Request $request)
    {
        $query = Post::with(['author:id,name', 'category:id,name,slug', 'tags', 'featuredMedia'])
            ->select('posts.*')
            ->leftJoin('categories', 'posts.category_id', '=', 'categories.id')
            ->leftJoin('users', 'posts.author_id', '=', 'users.id')
            ->when($request->status, fn ($q, $s) => $q->where('posts.status', $s))
            ->when($request->category, fn ($q, $c) => $q->where('posts.category_id', $c))
            ->when($request->search, fn ($q, $s) => $q->where('posts.title', 'LIKE', "%{$s}%"));

        // Default: tgl publikasi descending; bisa di-override lewat klik kolom.
        $sort = (string) $request->input('sort', 'published');
        $dir = strtolower((string) $request->input('dir', ''));
        $columns = $this->sortableColumns();
        [$column, $defaultDir] = $columns[$sort] ?? $columns['published'];
        $direction = in_array($dir, ['asc', 'desc'], true) ? $dir : $defaultDir;

        return $query->orderBy($column, $direction)->orderByDesc('posts.id');
    }

    public function create()
    {
        $categories = Category::active()->get();
        $tags = Tag::all();

        return view('admin.posts.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePost($request);

        $post = $this->postService->create($data, $request->user());

        if (! empty($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }

        if (! empty($data['media'])) {
            $post->media()->sync($data['media']);
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'module' => 'Posts',
            'action' => 'CREATE',
            'record_id' => $post->id,
            'description' => "Berita '{$post->title}' dibuat",
            'ip_hash' => hash('sha256', $request->ip() ?? ''),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil dibuat.',
            'data' => $post,
        ], 201);
    }

    public function show(Post $post)
    {
        $post->load(['author', 'category', 'tags', 'featuredMedia', 'media', 'reviewer']);

        return view('admin.posts.show', compact('post'));
    }

    /**
     * Public-look preview of an existing post, for any status.
     */
    public function preview(Post $post)
    {
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

    public function edit(Post $post)
    {
        $categories = Category::active()->get();
        $tags = Tag::all();

        return view('admin.posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $this->validatePost($request);

        $this->postService->update($post, $data);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'module' => 'Posts',
            'action' => 'UPDATE',
            'record_id' => $post->id,
            'description' => "Berita '{$post->title}' diupdate",
            'ip_hash' => hash('sha256', $request->ip() ?? ''),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil diupdate.',
            'data' => $post->refresh(),
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        $this->postService->delete($post);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'module' => 'Posts',
            'action' => 'DELETE',
            'record_id' => $post->id,
            'description' => "Berita '{$post->title}' dihapus",
            'ip_hash' => hash('sha256', $request->ip() ?? ''),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil dihapus.',
        ]);
    }

    public function submit(Request $request, Post $post)
    {
        $this->postService->submit($post, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil disubmit untuk review.',
        ]);
    }

    public function approve(Request $request, Post $post)
    {
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

        $this->postService->reject($post, $request->user(), $request->note);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil ditolak.',
        ]);
    }

    public function publish(Request $request, Post $post)
    {
        $this->postService->publish($post, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil dipublikasikan.',
        ]);
    }

    public function unpublish(Request $request, Post $post)
    {
        $this->postService->unpublish($post, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil diunpublish.',
        ]);
    }

    public function archive(Request $request, Post $post)
    {
        $this->postService->archive($post, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil diarsipkan.',
        ]);
    }

    private function validatePost(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'featured_media_id' => 'nullable|exists:media,id',
            'status' => 'nullable|in:draft,submitted,approved,published,archived',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'media' => 'nullable|array',
            'media.*' => 'exists:media,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);
    }
}

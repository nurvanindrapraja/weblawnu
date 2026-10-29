<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Post;
use App\Services\MediaService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(
        private MediaService $mediaService
    ) {}

    public function index(Request $request)
    {
        $media = $this->mediaQuery($request)->paginate(25)->withQueryString();

        return view('admin.media.index', compact('media') + [
            'publishedMediaIds' => $this->publishedMediaIds(),
        ]);
    }

    /**
     * Live (AJAX) filter for the media grid. Rendered from the
     * admin/reporter/redaktur media pages.
     */
    public function filter(Request $request)
    {
        $media = $this->mediaQuery($request)->paginate(25)->withQueryString();

        return response()->json([
            'success' => true,
            'rows' => view('admin.media.partials.grid', compact('media') + [
                'publishedMediaIds' => $this->publishedMediaIds(),
            ])->render(),
            'pagination' => (string) $media->links(),
            'page' => $media->currentPage(),
            'total' => $media->total(),
        ]);
    }

    /**
     * IDs of media attached to published posts (post_media pivot or
     * featured_media_id). Reporters must not delete these.
     *
     * @return int[]
     */
    private function publishedMediaIds(): array
    {
        $fromPivot = \DB::table('post_media')
            ->join('posts', 'posts.id', '=', 'post_media.post_id')
            ->where('posts.status', 'published')
            ->pluck('post_media.media_id');

        $featured = Post::where('status', 'published')
            ->whereNotNull('featured_media_id')
            ->pluck('featured_media_id');

        return $fromPivot->merge($featured)->all();
    }

    private function mediaQuery(Request $request)
    {
        return Media::with('uploader:id,name')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('original_name', 'LIKE', "%{$search}%")
                        ->orWhere('alt_text', 'LIKE', "%{$search}%")
                        ->orWhere('caption', 'LIKE', "%{$search}%");
                });
            })
            ->when($request->type, function ($q, $type) {
                if ($type === 'image') {
                    $q->where('mime_type', 'LIKE', 'image/%');
                } else {
                    $q->where('mime_type', 'NOT LIKE', 'image/%');
                }
            })
            // Newest first — id is deterministic, so files uploaded in the
            // same second still sort by upload order.
            ->orderByDesc('id');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,mp4|max:10240',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
        ]);

        $media = $this->mediaService->upload(
            $request->file('file'),
            $request->user(),
            $request->alt_text,
            $request->caption
        );

        return response()->json([
            'success' => true,
            'message' => 'Media berhasil diupload.',
            'data' => $media,
        ], 201);
    }

    public function show(Media $media)
    {
        return view('admin.media.show', compact('media'));
    }

    public function update(Request $request, Media $media)
    {
        $data = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
        ]);

        $this->mediaService->update($media, $data);

        return response()->json([
            'success' => true,
            'message' => 'Media berhasil diupdate.',
            'data' => $media->refresh(),
        ]);
    }

    public function destroy(Request $request, Media $media)
    {
        $user = $request->user();

        if (! $user->isAdmin()) {
            // Reporter hanya boleh menghapus miliknya sendiri, dan
            // hanya selama mediannya belum dipakai di berita published.
            abort_unless(
                $user->isReporter()
                    && $media->uploaded_by === $user->id
                    && ! $media->isUsedInPublishedPosts(),
                403,
                'Anda tidak dapat menghapus media ini.',
            );
        }

        $this->mediaService->delete($media, $user);

        return response()->json([
            'success' => true,
            'message' => 'Media berhasil dihapus.',
        ]);
    }
}

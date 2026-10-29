<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Services\GalleryService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class GalleryController extends Controller
{
    public function __construct(
        private GalleryService $galleryService
    ) {}

    public function index(Request $request)
    {
        $galleries = $this->galleriesQuery($request)
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => Gallery::count(),
            'draft' => Gallery::draft()->count(),
            'submitted' => Gallery::submitted()->count(),
            'published' => Gallery::published()->count(),
            'rejected' => Gallery::rejected()->count(),
        ];

        return view('admin.galleries.index', compact('galleries', 'counts'));
    }

    /**
     * AJAX endpoint: returns filtered table rows + pagination as HTML fragments.
     */
    public function filter(Request $request)
    {
        $galleries = $this->galleriesQuery($request)
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'rows' => view('admin.galleries.partials.rows', compact('galleries'))->render(),
            'pagination' => (string) $galleries->links(),
            'page' => $galleries->currentPage(),
            'total' => $galleries->total(),
        ]);
    }

    public function create()
    {
        return view('admin.galleries.create');
    }

    public function store(Request $request)
    {
        if ($error = $this->uploadError($request)) {
            return response()->json($error, 422);
        }

        $data = $this->validated($request);

        $gallery = $this->galleryService->create($data, $request->user(), $this->uploads($request));

        return response()->json([
            'success' => true,
            'message' => 'Galeri berhasil dibuat.',
            'data' => $gallery,
        ], 201);
    }

    public function edit(Gallery $gallery)
    {
        $gallery->load('images');

        return view('admin.galleries.edit', compact('gallery'));
    }

    public function update(Request $request, Gallery $gallery)
    {
        if ($error = $this->uploadError($request)) {
            return response()->json($error, 422);
        }

        $data = $this->validated($request);

        $this->galleryService->update(
            $gallery,
            $data,
            $request->user(),
            $request->input('existing_captions', []),
            array_map('intval', array_keys($request->input('remove_images', []))),
            $this->uploads($request),
        );

        return response()->json([
            'success' => true,
            'message' => 'Galeri berhasil diupdate.',
            'data' => $gallery->refresh(),
        ]);
    }

    public function destroy(Request $request, Gallery $gallery)
    {
        $this->galleryService->delete($gallery, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Galeri berhasil dihapus.',
        ]);
    }

    public function publish(Request $request, Gallery $gallery)
    {
        $this->galleryService->publish($gallery, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Galeri dipublikasikan.',
        ]);
    }

    public function unpublish(Request $request, Gallery $gallery)
    {
        $this->galleryService->unpublish($gallery, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Galeri diunpublish (kembali ke draft).',
        ]);
    }

    public function approve(Request $request, Gallery $gallery)
    {
        if ($gallery->status !== Gallery::STATUS_SUBMITTED) {
            return response()->json([
                'success' => false,
                'message' => 'Galeri tidak dapat disetujui.',
            ], 400);
        }

        $this->galleryService->approve($gallery, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Galeri disetujui & dipublikasikan.',
        ]);
    }

    public function reject(Request $request, Gallery $gallery)
    {
        $note = $request->validate(['note' => 'required|string|max:1000']);

        $this->galleryService->reject($gallery, $request->user(), $note['note']);

        return response()->json([
            'success' => true,
            'message' => 'Galeri ditolak.',
        ]);
    }

    /**
     * File yang melebihi upload_max_filesize PHP tidak lolos hingga validasi
     * (muncul error kabur "failed to upload"). Deteksi lebih awal dengan pesan jelas.
     */
    private function uploadError(Request $request): ?array
    {
        foreach ($request->file('images', []) as $i => $file) {
            if (! ($file instanceof UploadedFile) || $file->isValid()) {
                continue;
            }

            $limit = trim((string) ini_get('upload_max_filesize'));

            return [
                'success' => false,
                'message' => 'Gambar ke-'.($i + 1).' gagal diupload: ukuran file melebihi batas server ('.$limit.'). Kompres gambar atau perbesar upload_max_filesize pada server.',
            ];
        }

        return null;
    }

    private function galleriesQuery(Request $request)
    {
        return Gallery::withCount('images')
            ->with(['author:id,name'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where('title', 'LIKE', "%{$s}%"))
            ->orderByDesc('created_at');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'required|file|image|mimes:jpg,jpeg,png,webp,gif|max:10240',
        ]);
    }

    /**
     * @return array<int, array{file: UploadedFile, caption: string}>
     */
    private function uploads(Request $request): array
    {
        $files = $request->file('images', []);
        $captions = (array) $request->input('image_captions', []);
        $uploads = [];

        foreach ($files as $i => $file) {
            $uploads[] = [
                'file' => $file,
                'caption' => $captions[$i] ?? '',
            ];
        }

        return $uploads;
    }
}

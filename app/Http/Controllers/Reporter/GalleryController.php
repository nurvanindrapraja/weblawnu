<?php

namespace App\Http\Controllers\Reporter;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\User;
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
        $galleries = $this->galleriesQuery(auth()->user(), $request)
            ->paginate(15)
            ->withQueryString();

        return view('admin.galleries.index', [
            'galleries' => $galleries,
            'counts' => null, // reporter hanya melihat galerinya sendiri
        ]);
    }

    /**
     * AJAX endpoint: returns filtered table rows + pagination as HTML fragments.
     */
    public function filter(Request $request)
    {
        $galleries = $this->galleriesQuery(auth()->user(), $request)
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
            'message' => 'Galeri berhasil disimpan sebagai draft.',
            'data' => $gallery,
        ], 201);
    }

    public function edit(Gallery $gallery)
    {
        $this->authorizeGallery($gallery);

        if (! in_array($gallery->status, [Gallery::STATUS_DRAFT, Gallery::STATUS_REJECTED], true)) {
            abort(403, 'Galeri ini tidak dapat diedit.');
        }

        $gallery->load('images');

        return view('admin.galleries.edit', compact('gallery'));
    }

    public function update(Request $request, Gallery $gallery)
    {
        $this->authorizeGallery($gallery);

        if ($error = $this->uploadError($request)) {
            return response()->json($error, 422);
        }

        if (! in_array($gallery->status, [Gallery::STATUS_DRAFT, Gallery::STATUS_REJECTED], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat mengedit galeri draft atau yang ditolak.',
            ], 403);
        }

        $data = $this->validated($request);

        if ($gallery->status === Gallery::STATUS_REJECTED) {
            $data['status'] = Gallery::STATUS_DRAFT;
            $data['rejection_note'] = null;
        }

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

    public function submit(Request $request, Gallery $gallery)
    {
        $this->authorizeGallery($gallery);

        if (! in_array($gallery->status, [Gallery::STATUS_DRAFT, Gallery::STATUS_REJECTED], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya galeri draft atau yang ditolak yang dapat disubmit.',
            ], 403);
        }

        if ($gallery->images()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tambahkan minimal satu gambar sebelum submit.',
            ], 400);
        }

        $this->galleryService->submit($gallery, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Galeri berhasil disubmit untuk review redaktur.',
        ]);
    }

    public function destroy(Request $request, Gallery $gallery)
    {
        $this->authorizeGallery($gallery);

        if ($gallery->status !== Gallery::STATUS_DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat menghapus galeri draft.',
            ], 403);
        }

        $this->galleryService->delete($gallery, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Galeri berhasil dihapus.',
        ]);
    }

    private function galleriesQuery(User $user, Request $request)
    {
        return $user->galleries()
            ->withCount('images')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where('title', 'LIKE', "%{$s}%"))
            ->orderByDesc('updated_at');
    }

    private function authorizeGallery(Gallery $gallery): void
    {
        if ($gallery->author_id !== auth()->id()) {
            abort(403, 'Anda tidak dapat mengelola galeri milik reporter lain.');
        }
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

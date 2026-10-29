<?php

namespace App\Http\Controllers\Redaktur;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Services\GalleryService;
use Illuminate\Http\Request;

class GalleryReviewController extends Controller
{
    public function __construct(
        private GalleryService $galleryService
    ) {}

    public function index(Request $request)
    {
        $galleries = Gallery::withCount('images')
            ->with(['author:id,name'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where('title', 'LIKE', "%{$s}%"))
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => Gallery::count(),
            'submitted' => Gallery::submitted()->count(),
            'published' => Gallery::published()->count(),
            'rejected' => Gallery::rejected()->count(),
            'draft' => Gallery::draft()->count(),
        ];

        return view('redaktur.galleries.index', compact('galleries', 'counts'));
    }

    public function review(Gallery $gallery)
    {
        $gallery->load(['images', 'author:id,name']);

        return view('redaktur.galleries.show', compact('gallery'));
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
        if ($gallery->status !== Gallery::STATUS_SUBMITTED) {
            return response()->json([
                'success' => false,
                'message' => 'Galeri tidak dapat ditolak.',
            ], 400);
        }

        $data = $request->validate(['note' => 'required|string|max:1000']);

        $this->galleryService->reject($gallery, $request->user(), $data['note']);

        return response()->json([
            'success' => true,
            'message' => 'Galeri ditolak. Catatan telah disimpan.',
        ]);
    }
}

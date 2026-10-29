<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Page;
use App\Services\PageService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        private PageService $pageService
    ) {}

    public function index(Request $request)
    {
        $pages = Page::with('featuredMedia')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where('title', 'LIKE', "%{$s}%"))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_media_id' => 'nullable|exists:media,id',
            'status' => 'required|in:draft,published,archived',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $page = $this->pageService->create($data, $request->user());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'module' => 'Pages',
            'action' => 'CREATE',
            'record_id' => $page->id,
            'description' => "Halaman '{$page->title}' dibuat",
            'ip_hash' => hash('sha256', $request->ip() ?? ''),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Halaman berhasil dibuat.',
            'data' => $page,
        ], 201);
    }

    public function show(Page $page)
    {
        $page->load('featuredMedia', 'author');

        return view('admin.pages.show', compact('page'));
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_media_id' => 'nullable|exists:media,id',
            'status' => 'required|in:draft,published,archived',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $this->pageService->update($page, $data);

        return response()->json([
            'success' => true,
            'message' => 'Halaman berhasil diupdate.',
            'data' => $page->refresh(),
        ]);
    }

    public function destroy(Request $request, Page $page)
    {
        $this->pageService->delete($page);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'module' => 'Pages',
            'action' => 'DELETE',
            'record_id' => $page->id,
            'description' => "Halaman '{$page->title}' dihapus",
            'ip_hash' => hash('sha256', $request->ip() ?? ''),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Halaman berhasil dihapus.',
        ]);
    }

    public function publish(Request $request, Page $page)
    {
        $this->pageService->publish($page, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Halaman berhasil dipublikasikan.',
        ]);
    }

    public function unpublish(Request $request, Page $page)
    {
        $this->pageService->unpublish($page, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Halaman berhasil diunpublish.',
        ]);
    }
}

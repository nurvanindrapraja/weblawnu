<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        private MenuService $menuService
    ) {}

    public function index()
    {
        $menus = $this->menuService->getTreeStructure();

        return view('admin.menus.index', compact('menus'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'parent_id' => 'nullable|exists:menus,id',
            'type' => 'required|in:page,post,category,custom,dropdown',
            'url' => 'nullable|string|max:500',
            'target' => 'required|in:_self,_blank',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $menu = $this->menuService->create($data);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'module' => 'Menus',
            'action' => 'CREATE',
            'record_id' => $menu->id,
            'description' => "Menu '{$menu->title}' ditambahkan",
            'ip_hash' => hash('sha256', $request->ip() ?? ''),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil ditambahkan.',
            'data' => $menu,
        ], 201);
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'parent_id' => 'nullable|exists:menus,id',
            'type' => 'required|in:page,post,category,custom,dropdown',
            'url' => 'nullable|string|max:500',
            'target' => 'required|in:_self,_blank',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $this->menuService->update($menu, $data);

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil diupdate.',
            'data' => $menu->refresh(),
        ]);
    }

    public function destroy(Request $request, Menu $menu)
    {
        $this->menuService->delete($menu, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil dihapus.',
        ]);
    }

    public function toggleStatus(Request $request, Menu $menu)
    {
        $this->menuService->toggleStatus($menu, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Status menu berhasil diupdate.',
            'data' => ['is_active' => $menu->is_active],
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menus,id',
            'items.*.parent_id' => 'nullable|exists:menus,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        $this->menuService->reorder($request->items);

        return response()->json([
            'success' => true,
            'message' => 'Urutan menu berhasil diupdate.',
        ]);
    }
}

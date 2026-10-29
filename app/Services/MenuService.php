<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\User;

class MenuService
{
    public function create(array $data): Menu
    {
        return Menu::create($data);
    }

    public function update(Menu $menu, array $data): Menu
    {
        $menu->update($data);

        return $menu->refresh();
    }

    public function delete(Menu $menu, User $user): bool
    {
        $menu->delete();

        $this->logActivity($user, 'Menus', 'DELETE', $menu->id, "Menu '{$menu->title}' dihapus");

        return true;
    }

    public function reorder(array $items): void
    {
        foreach ($items as $item) {
            Menu::where('id', $item['id'])->update([
                'parent_id' => $item['parent_id'] ?? null,
                'sort_order' => $item['sort_order'] ?? 0,
            ]);
        }
    }

    public function move(Menu $menu, ?int $parentId, User $user): Menu
    {
        $menu->update(['parent_id' => $parentId]);

        $this->logActivity($user, 'Menus', 'MOVE', $menu->id, "Menu '{$menu->title}' dipindah parent");

        return $menu->refresh();
    }

    public function toggleStatus(Menu $menu, User $user): Menu
    {
        $menu->update(['is_active' => !$menu->is_active]);

        $this->logActivity($user, 'Menus', $menu->is_active ? 'ACTIVATE' : 'DEACTIVATE', $menu->id, "Menu '{$menu->title}' " . ($menu->is_active ? 'diaktifkan' : 'dinonaktifkan'));

        return $menu->refresh();
    }

    public function getTreeStructure(): \Illuminate\Support\Collection
    {
        $menus = Menu::active()->get();

        return $menus->whereNull('parent_id')
            ->map(function ($menu) use ($menus) {
                $menu->children = $menus->where('parent_id', $menu->id);

                return $menu;
            })
            ->values();
    }

    private function logActivity(User $user, string $module, string $action, ?int $recordId, string $description): void
    {
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'module' => $module,
            'action' => $action,
            'record_id' => $recordId,
            'description' => $description,
            'ip_hash' => hash('sha256', request()->ip() ?? ''),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);
    }
}

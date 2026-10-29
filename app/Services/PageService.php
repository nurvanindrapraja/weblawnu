<?php

namespace App\Services;

use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Str;

class PageService
{
    public function create(array $data, User $author): Page
    {
        $data['author_id'] = $author->id;
        $data['slug'] = $this->generateSlug($data['title']);
        $data['status'] = $data['status'] ?? 'draft';

        return Page::create($data);
    }

    public function update(Page $page, array $data): Page
    {
        if (isset($data['title']) && $data['title'] !== $page->title) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        $page->update($data);

        return $page->refresh();
    }

    public function delete(Page $page): bool
    {
        return $page->delete();
    }

    public function publish(Page $page, User $user): Page
    {
        $page->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->logActivity($user, 'Pages', 'PUBLISH', $page->id, "Halaman '{$page->title}' dipublikasikan");

        return $page->refresh();
    }

    public function unpublish(Page $page, User $user): Page
    {
        $page->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->logActivity($user, 'Pages', 'UNPUBLISH', $page->id, "Halaman '{$page->title}' diunpublish");

        return $page->refresh();
    }

    private function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $i = 1;

        while (Page::where('slug', $slug)->exists()) {
            $slug = $original . '-' . (++$i);
        }

        return $slug;
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

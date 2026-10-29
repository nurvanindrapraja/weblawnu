<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostService
{
    public function create(array $data, User $author): Post
    {
        $data['author_id'] = $author->id;
        $data['slug'] = $this->generateSlug($data['title']);
        $data['status'] = $data['status'] ?? 'draft';
        $data['views'] = 0;
        $data['rating_average'] = 0;
        $data['rating_count'] = 0;

        return Post::create($data);
    }

    /**
     * Render the public article view for a post regardless of its status,
     * so editors can see exactly how the page will look once published.
     */
    public function previewView(Post $post): View
    {
        return view('frontend.posts.show', [
            'post' => $post,
            'relatedPosts' => collect(),
            'previousPost' => null,
            'nextPost' => null,
            'preview' => true,
        ]);
    }

    public function update(Post $post, array $data): Post
    {
        if (isset($data['title']) && $data['title'] !== $post->title) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        $post->update($data);

        if (isset($data['tags']) && is_array($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }

        if (isset($data['media']) && is_array($data['media'])) {
            $post->media()->sync($data['media']);
        }

        return $post->refresh();
    }

    public function delete(Post $post): bool
    {
        return $post->delete();
    }

    public function submit(Post $post, User $user): Post
    {
        $post->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->logActivity($user, 'Posts', 'SUBMIT', $post->id, "Berita '{$post->title}' disubmit untuk review");

        return $post->refresh();
    }

    public function startReview(Post $post, User $reviewer): Post
    {
        $post->update([
            'status' => 'review',
            'reviewed_by' => $reviewer->id,
        ]);

        $this->logActivity($reviewer, 'Posts', 'START_REVIEW', $post->id, "Review dimulai untuk berita '{$post->title}'");

        return $post->refresh();
    }

    public function approve(Post $post, User $reviewer): Post
    {
        $post->update([
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_note' => null,
        ]);

        $this->logActivity($reviewer, 'Posts', 'APPROVE', $post->id, "Berita '{$post->title}' disetujui");

        return $post->refresh();
    }

    public function reject(Post $post, User $reviewer, string $note = ''): Post
    {
        $post->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_note' => $note,
        ]);

        $this->logActivity($reviewer, 'Posts', 'REJECT', $post->id, "Berita '{$post->title}' ditolak. Catatan: {$note}");

        return $post->refresh();
    }

    public function publish(Post $post, User $publisher): Post
    {
        $post->update([
            'status' => 'published',
            'published_by' => $publisher->id,
            'published_at' => now(),
        ]);

        $this->logActivity($publisher, 'Posts', 'PUBLISH', $post->id, "Berita '{$post->title}' dipublikasikan");

        return $post->refresh();
    }

    public function unpublish(Post $post, User $user): Post
    {
        $post->update([
            'status' => 'approved',
            'published_at' => null,
        ]);

        $this->logActivity($user, 'Posts', 'UNPUBLISH', $post->id, "Berita '{$post->title}' diunpublish");

        return $post->refresh();
    }

    public function archive(Post $post, User $user): Post
    {
        $post->update(['status' => 'archived']);

        $this->logActivity($user, 'Posts', 'ARCHIVE', $post->id, "Berita '{$post->title}' diarsipkan");

        return $post->refresh();
    }

    public function incrementViews(Post $post): void
    {
        $post->increment('views');
    }

    public function getRelated(Post $post, int $limit = 5): Collection
    {
        return Post::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take($limit)
            ->get(['id', 'title', 'slug', 'excerpt', 'featured_media_id', 'published_at', 'category_id']);
    }

    public function getPrevious(Post $post): ?Post
    {
        return Post::published()
            ->where('published_at', '<', $post->published_at)
            ->latest('published_at')
            ->first(['id', 'title', 'slug']);
    }

    public function getNext(Post $post): ?Post
    {
        return Post::published()
            ->where('published_at', '>', $post->published_at)
            ->oldest('published_at')
            ->first(['id', 'title', 'slug']);
    }

    private function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $i = 1;

        while (Post::where('slug', $slug)->exists()) {
            $slug = $original.'-'.(++$i);
        }

        return $slug;
    }

    private function logActivity(User $user, string $module, string $action, ?int $recordId, string $description): void
    {
        ActivityLog::create([
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

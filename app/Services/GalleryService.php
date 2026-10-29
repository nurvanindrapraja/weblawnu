<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class GalleryService
{
    public function create(array $data, User $author, array $uploadedImages = []): Gallery
    {
        $data['author_id'] = $author->id;
        $data['slug'] = $this->generateSlug($data['title']);
        $data['status'] = $data['status'] ?? Gallery::STATUS_DRAFT;

        $gallery = Gallery::create($data);
        $this->attachUploads($gallery, $uploadedImages, 0, $author);

        $this->logActivity($author, 'Galleries', 'CREATE', $gallery->id, "Galeri '{$gallery->title}' dibuat");

        return $gallery;
    }

    /**
     * @param  array  $data  field data (title, description, ...)
     * @param  array  $existingCaptions  [media_id => caption]
     * @param  array  $removedIds  media ids to detach
     * @param  array  $uploadedImages  list of ['file' => UploadedFile, 'caption' => string]
     */
    public function update(
        Gallery $gallery,
        array $data,
        User $user,
        array $existingCaptions = [],
        array $removedIds = [],
        array $uploadedImages = [],
    ): Gallery {
        if (isset($data['title']) && $data['title'] !== $gallery->title) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        $gallery->update($data);

        foreach ($existingCaptions as $mediaId => $caption) {
            $gallery->images()->updateExistingPivot($mediaId, ['caption' => $caption]);
        }

        foreach ($removedIds as $mediaId) {
            $gallery->images()->detach($mediaId);
        }

        $nextOrder = (int) $gallery->images()->max('sort_order');
        $this->attachUploads($gallery, $uploadedImages, $nextOrder + 1, $user);

        $this->logActivity($user, 'Galleries', 'UPDATE', $gallery->id, "Galeri '{$gallery->title}' diperbarui");

        return $gallery->refresh();
    }

    public function submit(Gallery $gallery, User $user): Gallery
    {
        $gallery->update([
            'status' => Gallery::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'rejection_note' => null,
        ]);

        $this->logActivity($user, 'Galleries', 'SUBMIT', $gallery->id, "Galeri '{$gallery->title}' disubmit untuk review");

        return $gallery->refresh();
    }

    /**
     * Redaktur menyetujui galeri — langsung dipublikasikan.
     */
    public function approve(Gallery $gallery, User $reviewer): Gallery
    {
        $gallery->update([
            'status' => Gallery::STATUS_PUBLISHED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'published_by' => $reviewer->id,
            'published_at' => now(),
            'rejection_note' => null,
        ]);

        $this->logActivity($reviewer, 'Galleries', 'APPROVE', $gallery->id, "Galeri '{$gallery->title}' disetujui & dipublikasikan");

        return $gallery->refresh();
    }

    public function reject(Gallery $gallery, User $reviewer, string $note = ''): Gallery
    {
        $gallery->update([
            'status' => Gallery::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_note' => $note,
        ]);

        $this->logActivity($reviewer, 'Galleries', 'REJECT', $gallery->id, "Galeri '{$gallery->title}' ditolak. Catatan: {$note}");

        return $gallery->refresh();
    }

    /**
     * Publikasi langsung (untuk admin tanpa lewat review redaktur).
     */
    public function publish(Gallery $gallery, User $publisher): Gallery
    {
        $gallery->update([
            'status' => Gallery::STATUS_PUBLISHED,
            'published_by' => $publisher->id,
            'published_at' => now(),
            'rejection_note' => null,
        ]);

        $this->logActivity($publisher, 'Galleries', 'PUBLISH', $gallery->id, "Galeri '{$gallery->title}' dipublikasikan");

        return $gallery->refresh();
    }

    public function unpublish(Gallery $gallery, User $user): Gallery
    {
        $gallery->update([
            'status' => Gallery::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $this->logActivity($user, 'Galleries', 'UNPUBLISH', $gallery->id, "Galeri '{$gallery->title}' diunpublish");

        return $gallery->refresh();
    }

    public function delete(Gallery $gallery, User $user): bool
    {
        $this->logActivity($user, 'Galleries', 'DELETE', $gallery->id, "Galeri '{$gallery->title}' dihapus");

        return (bool) $gallery->delete();
    }

    /**
     * @param  array  $uploadedImages  list of ['file' => UploadedFile, 'caption' => string]
     */
    private function attachUploads(Gallery $gallery, array $uploadedImages, int $startOrder, User $uploader): void
    {
        foreach ($uploadedImages as $item) {
            $file = $item['file'] ?? null;

            if (! ($file instanceof UploadedFile)) {
                continue;
            }

            $media = app(MediaService::class)->upload($file, $uploader, $file->getClientOriginalName());

            $gallery->images()->attach($media->id, [
                'caption' => trim((string) ($item['caption'] ?? '')),
                'sort_order' => $startOrder++,
            ]);
        }
    }

    private function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $i = 1;

        while (Gallery::where('slug', $slug)->exists()) {
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

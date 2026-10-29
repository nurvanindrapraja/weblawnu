<?php

namespace App\Services;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    public function upload(UploadedFile $file, User $uploader, ?string $altText = null, ?string $caption = null): Media
    {
        $path = $file->store('media/' . now()->format('Y/m'), 'public');

        $mime = $file->getMimeType();
        $isImage = str_starts_with($mime, 'image/');

        $width = null;
        $height = null;

        if ($isImage) {
            $size = getimagesize($file->getRealPath());
            $width = $size[0] ?? null;
            $height = $size[1] ?? null;
        }

        $media = Media::create([
            'uploaded_by' => $uploader->id,
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $mime,
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'alt_text' => $altText,
            'caption' => $caption,
        ]);

        $this->logActivity($uploader, 'Media', 'UPLOAD', $media->id, "Media '{$file->getClientOriginalName()} diupload");

        return $media;
    }

    public function delete(Media $media, User $user): bool
    {
        Storage::disk('public')->delete($media->path);
        $media->delete();

        $this->logActivity($user, 'Media', 'DELETE', $media->id, "Media '{$media->original_name}' dihapus");

        return true;
    }

    public function update(Media $media, array $data): Media
    {
        $media->update($data);

        return $media->refresh();
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

<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogService
{
    public function log(?User $user, string $module, string $action, ?int $recordId = null, ?string $description = null, ?Request $request = null): void
    {
        $request = $request ?? request();

        ActivityLog::create([
            'user_id' => $user?->id,
            'module' => $module,
            'action' => $action,
            'record_id' => $recordId,
            'description' => $description,
            'ip_hash' => hash('sha256', $request->ip() ?? ''),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);
    }

    public function getRecent(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return ActivityLog::with('user:id,name')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getByModule(string $module, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return ActivityLog::with('user:id,name')
            ->where('module', $module)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}

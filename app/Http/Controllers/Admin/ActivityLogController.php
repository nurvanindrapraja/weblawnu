<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = $this->logsQuery($request)
            ->paginate(50)
            ->withQueryString();

        $modules = ActivityLog::distinct()->pluck('module')->sort()->values();

        return view('admin.logs.index', compact('logs', 'modules'));
    }

    public function filter(Request $request)
    {
        $logs = $this->logsQuery($request)
            ->paginate(50)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'rows' => view('admin.logs.partials.rows', compact('logs'))->render(),
            'pagination' => (string) $logs->links(),
            'page' => $logs->currentPage(),
            'total' => $logs->total(),
        ]);
    }

    private function logsQuery(Request $request)
    {
        return ActivityLog::with('user:id,name')
            ->when($request->module, fn ($q, $m) => $q->where('module', $m))
            ->when($request->action, fn ($q, $a) => $q->where('action', $a))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('description', 'LIKE', "%{$search}%")
                        ->orWhere('action', 'LIKE', "%{$search}%")
                        ->orWhere('module', 'LIKE', "%{$search}%");
                });
            })
            ->orderByDesc('created_at');
    }
}

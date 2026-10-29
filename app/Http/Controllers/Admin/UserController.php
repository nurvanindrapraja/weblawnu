<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = $this->usersQuery($request)
            ->paginate(20)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function filter(Request $request)
    {
        $users = $this->usersQuery($request)
            ->paginate(20)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'rows' => view('admin.users.partials.rows', compact('users'))->render(),
            'pagination' => (string) $users->links(),
            'page' => $users->currentPage(),
            'total' => $users->total(),
        ]);
    }

    private function usersQuery(Request $request)
    {
        return User::with('roles')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('username', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            })
            ->orderByDesc('created_at');
    }

    public function create()
    {
        $roles = Role::all();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive',
        ]);

        $user = User::create($data);
        $user->roles()->sync([$data['role_id']]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'module' => 'Users',
            'action' => 'CREATE',
            'record_id' => $user->id,
            'description' => "User '{$user->name}' dibuat",
            'ip_hash' => hash('sha256', $request->ip() ?? ''),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan.',
                'data' => $user,
            ], 201);
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        $user->load('roles');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:30',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive',
        ]);

        $user->update($data);
        $user->roles()->sync([$data['role_id']]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'module' => 'Users',
            'action' => 'UPDATE',
            'record_id' => $user->id,
            'description' => "User '{$user->name}' diupdate",
            'ip_hash' => hash('sha256', $request->ip() ?? ''),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil diupdate.',
                'data' => $user->refresh(),
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diupdate.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun sendiri.',
            ], 400);
        }

        $user->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'module' => 'Users',
            'action' => 'DELETE',
            'record_id' => $user->id,
            'description' => "User '{$user->name}' dihapus",
            'ip_hash' => hash('sha256', $request->ip() ?? ''),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }

    public function toggleStatus(Request $request, User $user)
    {
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'module' => 'Users',
            'action' => $newStatus === 'active' ? 'ACTIVATE' : 'DEACTIVATE',
            'record_id' => $user->id,
            'description' => "User '{$user->name}' ".($newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan'),
            'ip_hash' => hash('sha256', $request->ip() ?? ''),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User '.($newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan').'.',
            'data' => ['status' => $newStatus],
        ]);
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => $request->password]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'module' => 'Users',
            'action' => 'RESET_PASSWORD',
            'record_id' => $user->id,
            'description' => "Password user '{$user->name}' direset",
            'ip_hash' => hash('sha256', $request->ip() ?? ''),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset.',
        ]);
    }
}

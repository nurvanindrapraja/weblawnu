<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('name')->get();
        $permissions = Permission::all();

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function create()
    {
        $permissions = Permission::all();

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50|alpha_dash|unique:roles,name',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'description' => $data['description'] ?? null,
        ]);

        if (!empty($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil ditambahkan.',
                'data' => $role,
            ], 201);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role berhasil ditambahkan.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('roles', 'name')->ignore($role->id)],
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'description' => $data['description'] ?? null,
        ]);

        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil diupdate.',
                'data' => $role->refresh(),
            ]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role berhasil diupdate.');
    }

    public function destroy(Request $request, Role $role)
    {
        if ($role->name === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Role admin tidak dapat dihapus.',
            ], 400);
        }

        $role->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role berhasil dihapus.');
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->permissions);

        return response()->json([
            'success' => true,
            'message' => 'Permissions berhasil diupdate.',
        ]);
    }
}

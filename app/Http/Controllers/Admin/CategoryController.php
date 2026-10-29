<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('posts')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['slug'] = $this->generateSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan.',
            'data' => $category,
        ], 201);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($data['name'] !== $category->name) {
            $data['slug'] = $this->generateSlug($data['name']);
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diupdate.',
            'data' => $category->refresh(),
        ]);
    }

    public function destroy(Request $request, Category $category)
    {
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }

    public function toggleStatus(Request $request, Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Status kategori berhasil diupdate.',
            'data' => ['is_active' => $category->is_active],
        ]);
    }

    private function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $original . '-' . (++$i);
        }

        return $slug;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('posts')->orderBy('name')->get();

        return view('admin.tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        // Block duplicate tags by name (case-insensitive). Tag uses SoftDeletes,
        // so this check is scoped to non-deleted records via the default global scope.
        $exists = Tag::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($data['name']))])->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Tag dengan nama tersebut sudah ada.',
            ], 422);
        }

        $data['slug'] = $this->generateSlug($data['name']);

        $tag = Tag::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Tag berhasil ditambahkan.',
            'data' => $tag,
        ], 201);
    }

    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        if ($data['name'] !== $tag->name) {
            $data['slug'] = $this->generateSlug($data['name']);
        }

        $tag->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Tag berhasil diupdate.',
            'data' => $tag->refresh(),
        ]);
    }

    public function destroy(Request $request, Tag $tag)
    {
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag berhasil dihapus.',
        ]);
    }

    private function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (Tag::where('slug', $slug)->exists()) {
            $slug = $original . '-' . (++$i);
        }

        return $slug;
    }
}

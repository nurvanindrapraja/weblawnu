<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Setting;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $galleries = Gallery::published()
            ->withCount('images')
            ->with('images:id,filename,path,alt_text')
            ->orderByDesc('published_at')
            ->paginate(Setting::get('posts_per_page', 9))
            ->withQueryString();

        return view('frontend.galleries.index', compact('galleries'));
    }

    public function show(Gallery $gallery)
    {
        abort_unless($gallery->isPublished(), 404);

        $gallery->load(['images', 'author:id,name']);

        $related = Gallery::published()
            ->where('id', '!=', $gallery->id)
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('frontend.galleries.show', compact('gallery', 'related'));
    }
}

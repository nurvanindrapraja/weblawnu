<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::published()
            ->where('slug', $slug)
            ->with(['featuredMedia', 'author'])
            ->firstOrFail();

        return view('frontend.pages.show', compact('page'));
    }
}

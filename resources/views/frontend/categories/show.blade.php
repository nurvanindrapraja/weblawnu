@extends('layouts.app')

@section('title', $category->name)
@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="/" class="hover:text-emerald-600">Home</a>
            <span>/</span>
            <a href="/berita" class="hover:text-emerald-600">Berita</a>
            <span>/</span>
            <span class="text-gray-900">{{ $category->name }}</span>
        </nav>

        <div class="sticky top-16 z-40 -mx-4 sm:-mx-6 lg:-mx-8 bg-gray-50 px-4 sm:px-6 lg:px-8 pt-4 pb-3 mb-5">
            <h1 class="text-3xl font-bold text-gray-900">{{ $category->name }}</h1>
            @if($category->description)
            <p class="text-gray-500 mt-1">{{ $category->description }}</p>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($posts as $post)
            <a href="/berita/{{ $post->slug }}" class="group bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <div class="aspect-video overflow-hidden bg-gray-200">
                    @if($post->featuredMedia)
                        <img src="{{ $post->featuredMedia->url }}" alt="{{ $post->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                        </div>
                    @endif
                </div>
                <div class="p-5">
                    <h2 class="text-lg font-semibold text-gray-900 group-hover:text-emerald-700 line-clamp-2">{{ $post->title }}</h2>
                    <p class="mt-2 text-sm text-gray-500 line-clamp-3">{{ $post->excerpt_text }}</p>
                    <p class="mt-3 text-xs text-gray-400">{{ $post->published_at?->format('d F Y') }}</p>
                </div>
            </a>
            @empty
            <div class="col-span-full bg-white rounded-xl p-12 text-center">
                <p class="text-gray-500">Belum ada berita di kategori ini.</p>
            </div>
            @endforelse
        </div>

        @if($posts->hasPages())
        <div class="mt-8">{{ $posts->links() }}</div>
        @endif
    </div>
@endsection

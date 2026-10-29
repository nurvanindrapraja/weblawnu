@extends('layouts.app')

@section('title', 'Berita')
@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="sticky top-16 z-40 -mx-4 sm:-mx-6 lg:-mx-8 bg-gray-50 px-4 sm:px-6 lg:px-8 pt-4 pb-3 mb-5">
            <h1 class="text-3xl font-bold text-gray-900">Berita</h1>
            <p class="text-gray-500 mt-1">Informasi dan berita terbaru dari LAWNU JATIM</p>
        </div>

        <!-- Category Filter -->
        <div class="flex flex-wrap gap-2 mb-8">
            <a href="/berita" class="px-4 py-2 text-sm rounded-full font-medium {{ request()->tag ? 'bg-white text-gray-700 hover:bg-gray-50' : 'bg-emerald-600 text-white' }}">
                Semua
            </a>
            @foreach(\App\Models\Category::active()->where('slug', '!=', 'dokumen')->get() as $cat)
            <a href="/kategori/{{ $cat->slug }}"
               class="px-4 py-2 text-sm rounded-full font-medium {{ request()->tag === $cat->slug ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                {{ $cat->name }}
            </a>
            @endforeach
        </div>

        <!-- Posts Grid -->
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
                    <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                        {{ $post->category?->name ?? 'Umum' }}
                    </span>
                    <h2 class="mt-3 text-lg font-semibold text-gray-900 group-hover:text-emerald-700 line-clamp-2">
                        {{ $post->title }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-500 line-clamp-3">{{ $post->excerpt_text }}</p>
                    <div class="mt-4 flex items-center justify-between text-xs text-gray-400">
                        <span>{{ $post->published_at?->format('d F Y') }}</span>
                        <span>{{ $post->views }} views</span>
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-full bg-white rounded-xl p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6m4 8h4M5 4h6a2 2 0 012 2v4a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"/></svg>
                <p class="text-gray-500">Belum ada berita.</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($posts->hasPages())
        <div class="mt-8">
            {{ $posts->links() }}
        </div>
        @endif
    </div>
@endsection

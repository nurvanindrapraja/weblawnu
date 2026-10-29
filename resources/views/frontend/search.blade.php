@extends('layouts.app')

@section('title', 'Pencarian')
@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="sticky top-16 z-40 -mx-4 sm:-mx-6 lg:-mx-8 bg-gray-50 px-4 sm:px-6 lg:px-8 pt-4 pb-3 mb-5">
            <h1 class="text-3xl font-bold text-gray-900">Hasil Pencarian</h1>
            @if($q)
            <p class="text-gray-500 mt-1">Mencari: "<span class="font-medium">{{ $q }}</span>" — {{ $totalResults }} hasil ditemukan</p>
            @endif
        </div>

        <!-- Search Form -->
        <form action="/pencarian" method="GET" class="mb-8">
            <div class="flex gap-2">
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari berita atau informasi..."
                       class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <button type="submit" class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium">
                    Cari
                </button>
            </div>
        </form>

        @if($totalResults > 0)
            <!-- Posts -->
            @if($results['posts']->isNotEmpty())
            <section class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Berita ({{ $results['posts']->count() }})</h2>
                <div class="space-y-4">
                    @foreach($results['posts'] as $post)
                    <a href="/berita/{{ $post->slug }}" class="group flex gap-4 p-4 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow">
                        @if($post->featuredMedia)
                        <div class="w-24 h-20 flex-shrink-0 rounded-lg overflow-hidden bg-gray-200">
                            <img src="{{ $post->featuredMedia->url }}" alt="{{ $post->title }}" class="w-full h-full object-cover" loading="lazy">
                        </div>
                        @endif
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-900 group-hover:text-emerald-700 line-clamp-2">{{ $post->title }}</h3>
                            <p class="text-sm text-gray-500 line-clamp-2 mt-1">{{ $post->excerpt_text }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $post->published_at?->format('d F Y') }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Pages -->
            @if($results['pages']->isNotEmpty())
            <section class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Halaman ({{ $results['pages']->count() }})</h2>
                <div class="space-y-3">
                    @foreach($results['pages'] as $page)
                    <a href="/halaman/{{ $page->slug }}" class="block p-4 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="font-medium text-gray-900 hover:text-emerald-700">{{ $page->title }}</h3>
                    </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Categories -->
            @if($results['categories']->isNotEmpty())
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-4">Kategori ({{ $results['categories']->count() }})</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($results['categories'] as $cat)
                    <a href="/kategori/{{ $cat->slug }}" class="px-4 py-2 bg-white rounded-lg shadow-sm text-gray-700 hover:text-emerald-700 hover:shadow-md transition-all">
                        {{ $cat->name }}
                    </a>
                    @endforeach
                </div>
            </section>
            @endif
        @else
            @if($q)
            <div class="bg-white rounded-xl p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <p class="text-gray-500">Tidak ada hasil untuk "{{ $q }}"</p>
            </div>
            @endif
        @endif
    </div>
@endsection

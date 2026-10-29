@extends('layouts.app')

@section('title', 'Galeri')
@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="sticky top-16 z-40 -mx-4 sm:-mx-6 lg:-mx-8 bg-gray-50 px-4 sm:px-6 lg:px-8 pt-4 pb-3 mb-5">
            <h1 class="text-3xl font-bold text-gray-900">Galeri Kegiatan</h1>
            <p class="text-gray-500 mt-1">Dokumentasi kegiatan LAWNU JATIM</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($galleries as $gallery)
            @php($cover = $gallery->images->first())
            <a href="/galeri/{{ $gallery->slug }}" class="group bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <div class="aspect-video overflow-hidden bg-gray-200">
                    @if($cover)
                        <img src="{{ $cover->url }}" alt="{{ $gallery->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                        </div>
                    @endif
                </div>
                <div class="p-5">
                    <h2 class="text-lg font-semibold text-gray-900 group-hover:text-emerald-700 line-clamp-2">
                        {{ $gallery->title }}
                    </h2>
                    @if($gallery->description)
                    <p class="mt-2 text-sm text-gray-500 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($gallery->description), 120) }}</p>
                    @endif
                    <div class="mt-4 flex items-center justify-between text-xs text-gray-400">
                        <span>{{ $gallery->event_date?->format('d F Y') ?? $gallery->published_at?->format('d F Y') }}</span>
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                            {{ $gallery->images_count ?? $gallery->images->count() }} foto
                        </span>
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-full bg-white rounded-xl p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                <p class="text-gray-500">Belum ada galeri kegiatan.</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($galleries->hasPages())
        <div class="mt-10">
            {{ $galleries->links() }}
        </div>
        @endif
    </div>
@endsection

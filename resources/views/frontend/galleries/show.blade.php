@extends('layouts.app')

@section('title', $gallery->meta_title ?: $gallery->title)
@section('meta_description', $gallery->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($gallery->description ?? ''), 155))
@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6" aria-label="Breadcrumb">
            <a href="/" class="hover:text-emerald-600">Home</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="/galeri" class="hover:text-emerald-600">Galeri</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 line-clamp-1">{{ $gallery->title }}</span>
        </nav>

        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ $gallery->title }}</h1>
            <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                @if($gallery->event_date)
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $gallery->event_date->format('d F Y') }}
                </span>
                @endif
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                    {{ $gallery->images->count() }} foto
                </span>
            </div>
        </header>

        @if($gallery->description)
        <div class="mb-8 text-gray-700 whitespace-pre-line text-sm leading-relaxed">
            {{ $gallery->description }}
        </div>
        @endif

        <!-- Gallery grid -->
        @if($gallery->images->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5" id="gallery-grid">
            @foreach($gallery->images as $image)
            <figure class="bg-white rounded-xl shadow-sm overflow-hidden">
                <button type="button" class="lightbox-trigger block w-full" data-full="{{ $image->url }}" data-caption="{{ $image->pivot->caption }}">
                    <div class="aspect-[4/3] overflow-hidden bg-gray-200">
                        <img src="{{ $image->url }}" alt="{{ $image->pivot->caption ?: $gallery->title }}"
                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-300" loading="lazy">
                    </div>
                </button>
                @if($image->pivot->caption)
                <figcaption class="px-4 py-3 text-sm text-gray-600 border-t border-gray-100">{{ $image->pivot->caption }}</figcaption>
                @endif
            </figure>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-xl p-12 text-center">
            <p class="text-gray-500">Galeri ini belum memiliki foto.</p>
        </div>
        @endif

        <!-- Related -->
        @if($related->isNotEmpty())
        <section class="mt-12">
            <h2 class="text-xl font-bold text-gray-900 mb-5">Kegiatan Lainnya</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                @foreach($related as $rel)
                @php($relCover = $rel->firstImage())
                <a href="/galeri/{{ $rel->slug }}" class="group bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    <div class="aspect-video overflow-hidden bg-gray-200">
                        @if($relCover)
                        <img src="{{ $relCover->url }}" alt="{{ $rel->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                        </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-emerald-700 line-clamp-2">{{ $rel->title }}</h3>
                        <p class="mt-1 text-xs text-gray-400">{{ $rel->event_date?->format('d F Y') ?? $rel->published_at?->format('d F Y') }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif
    </div>

    <!-- Simple lightbox -->
    <div id="lightbox" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 p-4" onclick="if(event.target===this)closeLightbox()">
        <button type="button" onclick="closeLightbox()" class="absolute top-4 right-4 p-2 text-white/80 hover:text-white" aria-label="Tutup">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="max-w-4xl max-h-full">
            <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[80vh] rounded-lg mx-auto">
            <p id="lightbox-caption" class="mt-3 text-center text-sm text-gray-200"></p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function openLightbox(src, caption) {
    const lb = document.getElementById('lightbox');
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox-caption').textContent = caption || '';
    lb.classList.remove('hidden');
    lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden');
    lb.classList.remove('flex');
    document.body.style.overflow = '';
}
document.querySelectorAll('.lightbox-trigger').forEach((btn) => {
    btn.addEventListener('click', () => openLightbox(btn.dataset.full, btn.dataset.caption));
});
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeLightbox(); });
</script>
@endpush
